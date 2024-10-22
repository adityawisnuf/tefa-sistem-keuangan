<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PembayaranSiswaResource;
use App\Http\Services\Duitku;
use App\Mail\PaymentSuccessMail;
use App\Models\Pembayaran;
use App\Models\PembayaranDuitku;
use App\Models\PembayaranKategori;
use App\Models\PembayaranSiswa;
use App\Models\PembayaranSiswaCicilan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PembayaranController extends Controller
{
    protected $duitku;
    public $months = [
        "Januari",
        "Februari",
        "Maret",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Agustus",
        "September",
        "Oktober",
        "November",
        "Desember"
    ];

    public function __construct(Duitku $duitku)
    {
        $this->duitku = $duitku;
    }
    public function index(Request $request)
    {
        $siswa = auth()->user()->siswa;

        $pembayaranKategori = PembayaranKategori::where('status', 1)
            ->whereHas('pembayaran', function ($query) use ($siswa) {
                $query->where('siswa_id', $siswa->id)
                    ->orWhere('kelas_id', $siswa->kelas_id);
            })
            ->with('pembayaran')
            ->paginate(10);

        return new PembayaranSiswaResource(true, 'List Pembayaran Siswa', [
            'pembayaran' => $pembayaranKategori,
        ]);
    }
    public function getPaymentMethod(string $pembayaran_id)
    {
        $yangHarusDiBayar = Pembayaran::findOrFail($pembayaran_id);
        $paymentMethods = $this->duitku->getPaymentMethod($yangHarusDiBayar->nominal);

        if (empty($paymentMethods)) {
            return response()->json(['error' => 'Payment method not found'], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return response()->json($paymentMethods);
    }
    public function batalTransaksi(Request $request, string $merchant_order_id)
    {
        DB::beginTransaction();
        try {
            $siswa = auth()->user()->siswa;
            if (!($request->get('cicil', false))) {
                PembayaranSiswa::where('merchant_order_id', $merchant_order_id)->delete();
                PembayaranDuitku::where('merchant_order_id', $merchant_order_id)->delete();
            } else {
                $pembayaran = PembayaranSiswa::where('pembayaran_id', $merchant_order_id)
                    ->where('siswa_id', $siswa->id)
                    ->whereNull('merchant_order_id')
                    ->first();
                if ($pembayaran) {
                    $lastCicilan = $pembayaran->pembayaran_siswa_cicilan()->latest()->first();

                    if ($lastCicilan && $lastCicilan->pembayaran_duitku->callback_response === null) {
                        $lastCicilan->delete();
                        $lastCicilan->pembayaran_duitku()->delete();
                    }
                }
            }

            DB::commit();
            return new PembayaranSiswaResource(true, 'Permintaan batal berhasil dilakukan!', null);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json(['error' => 'Gagal membatalkan transaksi: ' . $e->getMessage()], 500);
        }
    }
    public function duitkuCallbackHandler(Request $request)
    {
        // Check if the request comes from the correct User-Agent
        if (strtolower($request->header('User-Agent')) !== 'duitku callback agent') {
            return response('Unauthorized data manipulation detected, violating applicable laws', 403);
        }

        Log::debug(json_encode($request->all()));

        // Process the callback status
        $status = $this->duitku->callback($request->all());

        if (!$status) {
            return response('failure', 500); // Exit early if callback status fails
        }

        DB::beginTransaction();

        try {
            $merchant_order_id = $request->merchantOrderId;

            // Update PembayaranDuitku status
            PembayaranDuitku::where('merchant_order_id', $merchant_order_id)
                ->update([
                    'status' => '00',
                    'callback_response' => json_encode($request->all()),
                ]);

            // Fetch all relevant PembayaranSiswa records
            $pembayarans = PembayaranSiswa::where('merchant_order_id', $merchant_order_id)
                ->orWhereNull('merchant_order_id')
                ->get();

            foreach ($pembayarans as $pembayaran) {
                // Only process payments with status 0 (unpaid)
                if ($pembayaran->status === 0) {
                    $this->processPembayaranSiswa($pembayaran);
                }
            }

            DB::commit();

            // Send success email notification
            $user = $this->getRelatedUser($merchant_order_id);

            if ($user) {
                $this->sendPaymentSuccessMail($user, $request);
            }


            return response('success', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency(json_encode($e));
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Processes the payment for a PembayaranSiswa record.
     */
    protected function processPembayaranSiswa($pembayaran)
    {
        // Check if there are existing installments (cicilan)
        $hasCicilan = PembayaranSiswaCicilan::where('pembayaran_siswa_id', $pembayaran->id)->exists();

        if ($hasCicilan) {
            // Calculate the total installments (cicilan) amount
            $totalCicilan = PembayaranSiswaCicilan::where('pembayaran_siswa_id', $pembayaran->id)
                ->sum('nominal_cicilan');

            // Mark the payment as complete if the total installments cover the nominal amount
            if ($totalCicilan >= $pembayaran->nominal) {
                $pembayaran->status = 1; // Paid
                $pembayaran->save();
            }
        } else {
            // No installments, mark the payment as paid
            $pembayaran->status = 1; // Paid
            $pembayaran->save();
        }
    }

    /**
     * Retrieve the user related to the payment by merchant order ID.
     */
    protected function getRelatedUser($merchant_order_id)
    {
        // Get the first PembayaranDuitku record associated with the order ID
        $pembayaran_duitku = PembayaranDuitku::where('merchant_order_id', $merchant_order_id)->first();

        // If no PembayaranDuitku found, return null
        if (!$pembayaran_duitku) {
            return null;
        }

        // Try to get the related PembayaranSiswa
        $pembayaran_siswa = $pembayaran_duitku->pembayaran_siswa()->first();
        if ($pembayaran_siswa) {
            return $pembayaran_siswa->siswa->user ?? null;
        }

        // Try to get the related PembayaranSiswaCicilan if no PembayaranSiswa found
        $pembayaran_siswa_cicilan = $pembayaran_duitku->pembayaran_siswa_cicilan()->first();
        if ($pembayaran_siswa_cicilan) {
            return $pembayaran_siswa_cicilan->siswa->user ?? null;
        }

        return null; // Return null if no related user is found
    }


    /**
     * Sends the payment success email.
     */
    protected function sendPaymentSuccessMail($user, $request)
    {
        $siswa = $user->siswa;
        $sekolah = $siswa->kelas->sekolah;

        Mail::to($user)->send(new PaymentSuccessMail(
            nama_sekolah: $sekolah->nama,
            logo_sekolah: $sekolah->logo ?? "assets/sekolah/default.png",
            user_name: $user->name,
            nominal: $request->amount,
            ds_code: $request->reference,
            merchant_order_id: $request->merchantOrderId,
            customer_name: $request->cardName ?? $user->name,
            payment_method: $request->paymentCode,
            payment_name: $request->productDetail,
            payment_time: $request->settlementDate,
            payment_status: $request->transactionState . " " . $request->transactionStateStatus
        ));
    }

        public function getRiwayat(Request $request)
        {
            $siswa = auth()->user()->siswa;


            $data = PembayaranSiswa::where('siswa_id', $siswa->id)
                ->where('pembayaran_siswa.status', 1)
                ->join('pembayaran_duitku', 'pembayaran_siswa.merchant_order_id', '=', 'pembayaran_duitku.merchant_order_id', 'left')
                ->with('pembayaran.pembayaran_kategori', 'siswa.user', 'siswa.kelas')
                ->select('pembayaran_siswa.*', 'pembayaran_duitku.*')
                ->get();

            return response()->json([
                'message' => 'Riwayat pembayaran berhasil didapatkan',
                'data' => $data,
            ]);
        }
    public function getRiwayatTahunan(Request $request)
    {
        $siswa = auth()->user()->siswa;

        $data = PembayaranSiswa::where('pembayaran_siswa.siswa_id', $siswa->id)
            ->where('pembayaran_siswa.status', 1)
            ->join('pembayaran', 'pembayaran_siswa.pembayaran_id', '=', 'pembayaran.id')
            ->join('pembayaran_kategori', 'pembayaran.pembayaran_kategori_id', '=', 'pembayaran_kategori.id')
            ->leftJoin('pembayaran_duitku', 'pembayaran_siswa.merchant_order_id', '=', 'pembayaran_duitku.merchant_order_id')
            ->where('pembayaran_kategori.jenis_pembayaran', 2)
            ->select(
                'pembayaran_siswa.*',
                'pembayaran_duitku.*',
                'pembayaran_kategori.nama',
                'pembayaran.created_at AS pembayaran_created_at' // Alias pembayaran.created_at
            )
            ->get();



        return response()->json([
            'message' => 'Riwayat pembayaran berhasil didapatkan',
            'data' => $data,
        ]);
    }
    public function requestTransaksi(Request $request)
    {
        $validate = $request->validate([
            'payment_method' => ['string', 'required'],
            'return_url' => ['string', 'nullable'],
            'id' => ['integer', 'exists:pembayaran,id'],
        ]);

        DB::beginTransaction();

        try {
            $siswa = auth()->user()->siswa;
            $pembayaran = Pembayaran::with('pembayaran_kategori')->findOrFail($validate['id']);
            $merchant_order_id = 'TFKT-0-' . time();


            $data = [
                'merchantOrderId' => $merchant_order_id,
                'item_details' => [
                    [
                        'name' => $pembayaran->pembayaran_kategori->nama,
                        'price' => $pembayaran->nominal,
                        'quantity' => 1,
                    ],
                ],
                'payment_amount' => $pembayaran->nominal,
                'user' => [
                    'name' => $siswa->nama_depan . ' ' . $siswa->nama_belakang,
                    'email' => auth()->user()->email,
                    'phone' => $siswa->telepon,
                ],
                'payment_method' => $validate['payment_method'],
                'return_url' => $validate['return_url'],
                'title' => 'Pembayaran Siswa ' . $siswa->nama_depan,
            ];

            $hasil = $this->duitku->requestTransaction($data);

            PembayaranDuitku::create([
                'merchant_order_id' => $merchant_order_id,
                'reference' => $hasil['reference'],
                'payment_method' => $validate['payment_method'],
                'transaction_response' => json_encode($hasil),
                'status' => '00',
            ]);
            PembayaranSiswa::create([
                'siswa_id' => $siswa->id,
                'pembayaran_id' => $pembayaran->id,
                'nominal' => $pembayaran->nominal,
                'merchant_order_id' => $merchant_order_id,
                'status' => 0,
            ]);


            DB::commit();

            return new PembayaranSiswaResource(true, 'Permintaan bayar berhasil dilakukan!', $hasil);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
        }
    }
    function requestTransaksiCicilan(Request $request)
    {
        $validate = $request->validate([
            'payment_method' => ['string', 'required'],
            'return_url' => ['string', 'nullable'],
            'id' => ['integer', 'exists:pembayaran,id'],
            'angsuran' => ['numeric', 'min:1', 'max:12'],
        ]);

        DB::beginTransaction();

        // try {
        $siswa = auth()->user()->siswa;
        $pembayaran = Pembayaran::with('pembayaran_kategori')->where('pembayaran_kategori.jenis_pembayaran', 2)->findOrFail($validate['id']);

        $merchant_order_id = null;
        $pembayaran_siswa_cicilan = null;
        $pembayaran_siswa = null;

        $nominal = round($pembayaran->nominal / $validate['angsuran'], 0);
        $pembayaran_siswa_exist = PembayaranSiswa::where('siswa_id', $siswa->id)
            ->where('pembayaran_id', $pembayaran->id)
            ->where('status', 0)
            ->first();

        $name = null;

        if ($pembayaran_siswa_exist) {
            // Existing installment found, update it
            $merchant_order_id = 'TFKC-' . $pembayaran_siswa_exist->id . '-' . time();

            if (PembayaranSiswaCicilan::where('pembayaran_siswa_id', $pembayaran_siswa_exist->id)->count() > 0) {
                if ($pembayaran_siswa_exist->jumlah_tercicil < $pembayaran_siswa_exist->angsuran_cicilan) {
                    // Increment installment count
                    $pembayaran_siswa_exist->update(['jumlah_tercicil' => $pembayaran_siswa_exist->jumlah_tercicil + 1]);
                } else {
                    return response()->json(['success' => false, 'message' => 'Anda tidak dapat membuat cicilan baru!'], 400);
                }
            }

            // Create new installment
            $pembayaran_siswa_cicilan = PembayaranSiswaCicilan::create([
                'pembayaran_siswa_id' => $pembayaran_siswa_exist->id,
                'nominal_cicilan' => $nominal,
                'merchant_order_id' => $merchant_order_id,
            ]);
            $name = 'Cicilan ' . $pembayaran->pembayaran_kategori->nama . ' Ke-' . $pembayaran_siswa_exist->jumlah_tercicil;
        } else {
            // Create new payment record for the student
            $pembayaran_siswa = PembayaranSiswa::create([
                'siswa_id' => $siswa->id,
                'pembayaran_id' => $pembayaran->id,
                'nominal' => $pembayaran->nominal,
                'merchant_order_id' => null,
                'angsuran_cicilan' => $validate['angsuran'],
                'jumlah_tercicil' => 1,
                'status' => 0,
            ]);
            $merchant_order_id = 'TFKC-' . $pembayaran_siswa->id . '-' . time();
            $pembayaran_siswa_cicilan = PembayaranSiswaCicilan::create([
                'pembayaran_siswa_id' => $pembayaran_siswa->id,
                'nominal_cicilan' => $nominal,
                'merchant_order_id' => $merchant_order_id,
            ]);
            $name = 'Cicilan ' . $pembayaran->pembayaran_kategori->nama . ' Ke-1';
        }

        // Prepare transaction data
        $data = [
            'merchantOrderId' => $merchant_order_id,
            'item_details' => [
                [
                    'name' => $name,
                    'price' => $nominal,
                    'quantity' => 1,
                ],
            ],
            'payment_amount' => $nominal,
            'user' => [
                'name' => $siswa->nama_depan . ' ' . $siswa->nama_belakang,
                'email' => auth()->user()->email,
                'phone' => $siswa->telepon,
            ],
            'payment_method' => $validate['payment_method'],
            'return_url' => $request->return_url,
            'title' => 'Pembayaran Siswa ' . $siswa->nama_depan,
        ];

        // Call external payment service (Duitku)
        $hasil = $this->duitku->requestTransaction($data);

        // Save transaction to the database
        PembayaranDuitku::create([
            'merchant_order_id' => $merchant_order_id,
            'reference' => $hasil['reference'],
            'payment_method' => $validate['payment_method'],
            'transaction_response' => json_encode($hasil),
            'status' => 00,
        ]);

        // Update installment with the correct merchant_order_id
        $pembayaran_siswa_cicilan->update(['merchant_order_id' => $merchant_order_id]);

        DB::commit();

        return new PembayaranSiswaResource(true, 'Permintaan bayar berhasil dilakukan!', $hasil);
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     Log::error("Error in requestTransaksiCicilan: " . $e->getMessage());
        //     return response()->json(['error' => 'Terjadi kesalahan dalam pemrosesan transaksi!'], 500);
        // }
    }
    public function getCurrent(Request $request)
    {
        $siswa = auth()->user()->siswa;

        // First, prioritize pembayaran with matching siswa_id
        $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
            $query->where('jenis_pembayaran', 1)
                ->where('status', 1); // Active payment categories
        })
            ->where('siswa_id', $siswa->id)->orWhere('kelas_id', $siswa->kelas->id)
            ->with(['pembayaran_siswa' => function ($query) use ($siswa) {
                $query->where('siswa_id', $siswa->id)
                    ->with('pembayaran_siswa_cicilan'); // Include cicilan details
            }, 'pembayaran_kategori'])
            ->get();

        // If no pembayaran found for siswa_id, fallback to those matching kelas_id
        if ($pembayaranList->isEmpty()) {
            $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
                $query->where('jenis_pembayaran', 1) // jenis_pembayaran for 'tahunan'
                    ->where('status', 1); // Active payment categories
            })
                ->where('kelas_id', $siswa->kelas_id)
                ->with(['pembayaran_siswa' => function ($query) use ($siswa) {
                    $query->where('siswa_id', $siswa->id)
                        ->with('pembayaran_siswa_cicilan'); // Include cicilan details
                }, 'pembayaran_kategori'])
                ->get();
        }

        $responseList = [];

        foreach ($pembayaranList as $pembayaran) {
            $pembayaran_siswa = $pembayaran->pembayaran_siswa->first();

            $totalCicilan = 0;
            $duitkuTransactions = [];

            if ($pembayaran_siswa && $pembayaran_siswa->pembayaran_siswa_cicilan->count() > 0) {
                $totalCicilan = $pembayaran_siswa->pembayaran_siswa_cicilan->sum('nominal_cicilan');
                foreach ($pembayaran_siswa->pembayaran_siswa_cicilan as $cicilan) {
                    $duitkuTransactions[] = $cicilan->pembayaran_duitku();
                }
            } elseif ($pembayaran_siswa) {
                $duitkuTransactions = PembayaranDuitku::where('merchant_order_id', $pembayaran_siswa->merchant_order_id)->get();
            }

            $responseList[] = [
                'id' => $pembayaran->id,
                'nama' => $pembayaran->pembayaran_kategori->nama,
                'tanggal_bayar' => $pembayaran->pembayaran_kategori->tanggal_pembayaran,
                'nominal' => $pembayaran->nominal,
                'transaction_requested' => $pembayaran_siswa ? true : false,
                'paid' => $pembayaran_siswa ? $pembayaran_siswa->status : false,
                'merchant_order_id' => $pembayaran_siswa ? $pembayaran_siswa->merchant_order_id : null,
                'pembayaran_ke' => $pembayaran->pembayaran_ke,
                'bulan' => $this->months[$pembayaran->pembayaran_ke - 1],
                'total_cicilan' => $totalCicilan,
                'duitku' => $duitkuTransactions,
            ];
        }

        // Filter invalid payments
        $filteredPembayaranList = collect($responseList)->filter(function ($pembayaran) {
            return $pembayaran['paid'] === false || $pembayaran['paid'] === 0;
        });

        return response()->json([
            'success' => true,
            'nama' => $siswa->nama_depan . ($siswa->nama_belakang ? ' ' . $siswa->nama_belakang : ''),
            'jurusan' => $siswa->kelas->jurusan,
            'kelas' => $siswa->kelas->kelas,
            'orang_tua' => $siswa->orangtua->nama ?? "",
            'message' => 'List Pembayaran Siswa',
            'data' => $filteredPembayaranList,
        ]);
    }
    public function getCurrentYear(Request $request)
    {
        $siswa = auth()->user()->siswa;

        // First, prioritize pembayaran with matching siswa_id
        $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
            $query->where('jenis_pembayaran', 2)
                ->where('status', 1) // Active payment categories
                ->whereYear('created_at', now()->year); // Current year
        })
            ->where(function ($query) use ($siswa) {
                // Group siswa_id and kelas_id conditions
                $query->where('siswa_id', $siswa->id)
                    ->orWhere('kelas_id', $siswa->kelas->id);
            })
            ->with([
                'pembayaran_siswa' => function ($query) use ($siswa) {
                    $query->where('siswa_id', $siswa->id)
                        ->with('pembayaran_siswa_cicilan'); // Include cicilan details
                },
                'pembayaran_kategori'
            ])
            ->get();


        // If no pembayaran found for siswa_id, fallback to those matching kelas_id
        if ($pembayaranList->isEmpty()) {
            $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
                $query->where('jenis_pembayaran', 2) // jenis_pembayaran for 'tahunan'
                    ->where('status', 1); // Active payment categories
            })
                ->where('kelas_id', $siswa->kelas_id)
                ->with(['pembayaran_siswa' => function ($query) use ($siswa) {
                    $query->where('siswa_id', $siswa->id)
                        ->with('pembayaran_siswa_cicilan'); // Include cicilan details
                }, 'pembayaran_kategori'])
                ->get();
        }

        $responseList = [];

        foreach ($pembayaranList as $pembayaran) {
            $pembayaran_siswa = $pembayaran->pembayaran_siswa->first();

            $totalCicilan = 0;
            $duitkuTransactions = [];

            if ($pembayaran_siswa && $pembayaran_siswa->pembayaran_siswa_cicilan->count() > 0) {
                $totalCicilan = $pembayaran_siswa->pembayaran_siswa_cicilan->sum('nominal_cicilan');
                foreach ($pembayaran_siswa->pembayaran_siswa_cicilan as $cicilan) {
                    $duitkuTransactions[] = $cicilan->pembayaran_duitku();
                }
            } elseif ($pembayaran_siswa) {
                $duitkuTransactions = PembayaranDuitku::where('merchant_order_id', $pembayaran_siswa->merchant_order_id)->get();
            }

            $responseList[] = [
                'id' => $pembayaran->id,
                'nama' => $pembayaran->pembayaran_kategori->nama,
                'tanggal_bayar' => $pembayaran->pembayaran_kategori->tanggal_pembayaran,
                'nominal' => $pembayaran->nominal,
                'transaction_requested' => $pembayaran_siswa ? true : false,
                'paid' => $pembayaran_siswa ? $pembayaran_siswa->status : false,
                'merchant_order_id' => $pembayaran_siswa ? $pembayaran_siswa->merchant_order_id : null,
                'total_cicilan' => $totalCicilan,
                'duitku' => $duitkuTransactions,
            ];
        }

        // Filter invalid payments
        $filteredPembayaranList = collect($responseList)->filter(function ($pembayaran) {
            return $pembayaran['paid'] === false || $pembayaran['paid'] === 0;
        });

        return response()->json([
            'success' => true,
            'nama' => $siswa->nama_depan . ($siswa->nama_belakang ? ' ' . $siswa->nama_belakang : ''),
            'jurusan' => $siswa->kelas->jurusan,
            'kelas' => $siswa->kelas->kelas,
            'orang_tua' => $siswa->orangtua->nama ?? "",
            'message' => 'List Pembayaran Siswa',
            'data' => $filteredPembayaranList,
        ]);
    }
    public function getCicilans(Request $request)
    {
        $siswa = auth()->user()->siswa;

        // First, prioritize pembayaran with matching siswa_id
        $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
            $query->where('jenis_pembayaran', 2)
                ->where('status', 1) // Active payment categories
                ->whereYear(now()->years); // Active payment categories
        })
            ->where('siswa_id', $siswa->id)->orWhere('kelas_id', $siswa->kelas->id)
            ->with(['pembayaran_siswa' => function ($query) use ($siswa) {
                $query->where('siswa_id', $siswa->id)
                    ->with('pembayaran_siswa_cicilan'); // Include cicilan details
            }, 'pembayaran_kategori'])
            ->get();

        // If no pembayaran found for siswa_id, fallback to those matching kelas_id
        if ($pembayaranList->isEmpty()) {
            $pembayaranList = Pembayaran::whereHas('pembayaran_kategori', function ($query) {
                $query->where('jenis_pembayaran', 2) // jenis_pembayaran for 'tahunan'
                    ->where('status', 1); // Active payment categories
            })
                ->where('kelas_id', $siswa->kelas_id)
                ->with(['pembayaran_siswa' => function ($query) use ($siswa) {
                    $query->where('siswa_id', $siswa->id)
                        ->with('pembayaran_siswa_cicilan'); // Include cicilan details
                }, 'pembayaran_kategori'])
                ->get();
        }

        $responseList = [];

        foreach ($pembayaranList as $pembayaran) {
            $pembayaran_siswa = $pembayaran->pembayaran_siswa->first();

            $totalCicilan = 0;
            $duitkuTransactions = [];

            if ($pembayaran_siswa && $pembayaran_siswa->pembayaran_siswa_cicilan->count() > 0) {
                $totalCicilan = $pembayaran_siswa->pembayaran_siswa_cicilan->sum('nominal_cicilan');
                foreach ($pembayaran_siswa->pembayaran_siswa_cicilan as $cicilan) {
                    $duitkuTransactions[] = $cicilan->pembayaran_duitku();
                }
            } elseif ($pembayaran_siswa) {
                $duitkuTransactions = PembayaranDuitku::where('merchant_order_id', $pembayaran_siswa->merchant_order_id)->get();
            }

            $responseList[] = [
                'id' => $pembayaran->id,
                'nama' => $pembayaran->pembayaran_kategori->nama,
                'tanggal_bayar' => $pembayaran->pembayaran_kategori->tanggal_pembayaran,
                'nominal' => $pembayaran->nominal,
                'transaction_requested' => $pembayaran_siswa ? true : false,
                'paid' => $pembayaran_siswa ? $pembayaran_siswa->status : false,
                'merchant_order_id' => $pembayaran_siswa ? $pembayaran_siswa->merchant_order_id : null,
                'total_cicilan' => $totalCicilan,
                'duitku' => $duitkuTransactions,
            ];
        }

        // Filter invalid payments
        $filteredPembayaranList = collect($responseList)->filter(function ($pembayaran) {
            return $pembayaran['paid'] === false || $pembayaran['paid'] === 0;
        });

        return response()->json([
            'success' => true,
            'nama' => $siswa->nama_depan . ($siswa->nama_belakang ? ' ' . $siswa->nama_belakang : ''),
            'jurusan' => $siswa->kelas->jurusan,
            'kelas' => $siswa->kelas->kelas,
            'orang_tua' => $siswa->orangtua->nama ?? "",
            'message' => 'List Pembayaran Siswa',
            'data' => $filteredPembayaranList,
        ]);
    }
}