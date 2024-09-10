<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PembayaranSiswaResource;
use App\Http\Services\Duitku;
use App\Models\Pembayaran;
use App\Models\PembayaranDuitku;
use App\Models\PembayaranKategori;
use App\Models\PembayaranSiswa;
use App\Models\PembayaranSiswaCicilan;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembayaranController extends Controller
{
    protected $duitku;

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

    public function getCurrent(Request $request)
    {
        $siswa = auth()->user()->siswa;

        // Fetch active payment categories where the student is involved
        $pembayaranKategori = PembayaranKategori::where('status', 1)
            ->whereHas('pembayaran', function ($query) use ($siswa) {
                $query->where('siswa_id', $siswa->id)
                    ->orWhere('kelas_id', $siswa->kelas_id);
            })
            ->where('jenis_pembayaran', 1)
            ->with(['pembayaran' => function ($query) use ($siswa) {
                $query->with(['pembayaran_siswa' => function ($query) use ($siswa) {
                    $query->where('siswa_id', $siswa->id);
                }, 'pembayaran_siswa.pembayaran_siswa_cicilan']); // Include cicilan data
            }])
            ->first();

        // Check if $pembayaranKategori exists and has pembayaran data
        if (! $pembayaranKategori || ! $pembayaranKategori->pembayaran->first()) {
            return response()->json(['success' => false, 'message' => 'No pembayaran data found'], 404);
        }

        // Retrieve the pembayaran list
        $pembayaranList = $pembayaranKategori->pembayaran->map(function ($pembayaran) use ($pembayaranKategori, $siswa) {
            // Check if the student has paid this pembayaran
            $pembayaranSiswa = $pembayaran->pembayaran_siswa->first(); // This can be null

            // Get total installment (cicilan) amount if any
            $totalCicilan = 0;
            $duitkuTransactions = [];

            if ($pembayaranSiswa && $pembayaranSiswa->pembayaran_siswa_cicilan->count() > 0) {
                $totalCicilan = $pembayaranSiswa->pembayaran_siswa_cicilan->sum('nominal_cicilan');
                foreach ($pembayaranSiswa->pembayaran_siswa_cicilan as  $value) {
                    $duitkuTransactions[] = $value->pembayaran_duitku;
                }
            } elseif ($pembayaranSiswa) {
                $duitkuTransactions = PembayaranDuitku::where('merchant_order_id', $pembayaranSiswa->merchant_order_id)->get();
            }



            return [
                'id' => $pembayaran->id,
                'nama' => $pembayaranKategori->nama,
                'tanggal_bayar' => $pembayaranKategori->tanggal_pembayaran,
                'nominal' => $pembayaran->nominal,
                'siswa_sudah_bayar' => $pembayaranSiswa ? true : false,
                'pembayaran_valid' => $pembayaranSiswa ? $pembayaranSiswa->status : false,
                'merchant_order_id' => $pembayaranSiswa ? $pembayaranSiswa->merchant_order_id : null,
                'total_cicilan' => $totalCicilan, // Total paid installments
                'duitku' => $duitkuTransactions,  // All related Duitku transactions
            ];
        });

        return response()->json([
            'success' => true,
            'nama' => $siswa->nama_depan . ($siswa->nama_belakang ? ' ' . $siswa->nama_belakang : ''),
            'jurusan' => $siswa->kelas->jurusan,
            'kelas' => $siswa->kelas->kelas,
            'orang_tua' => $siswa->orangtua->nama,
            'message' => 'List Pembayaran Siswa',
            'data' => [
                'pembayaran' => $pembayaranList,
                'siswa_sudah_bayar_bulan_ini' => $pembayaranList->contains('siswa_sudah_bayar', true),
            ],
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
                    ->get();

                foreach ($pembayaran as $item) {
                    $item->pembayaran_siswa_cicilan()->delete();
                    $item->delete();
                }
            }
            DB::commit();

            return new PembayaranSiswaResource(true, 'Permintaan batal berhasil dilakukan!', null);
        } catch (\Exception $e) {
            // Roll back the transaction if something goes wrong
            DB::rollBack();

            return response()->json(['error' => 'Gagal membatalkan transaksi: ' . $e->getMessage()], 500);
        }
    }

    public function duitkuCallbackHandler(Request $request)
    {
        if (strtolower($request->header('User-Agent')) !== 'duitku callback agent') {
            return response('ANDA TELAH MELAKUKAN MANIPULASI DATA YANG MELANGGAR UU ITE YANG BERLAKU', 403);
        }

        Log::debug(json_encode($request->all()));
        $status = $this->duitku->callback($request->all());

        if ($status) {
            DB::beginTransaction();

            try {
                $merchantOrderId = $request->merchantOrderId;

                // Update PembayaranDuitku with callback response
                PembayaranDuitku::where('merchant_order_id', $merchantOrderId)
                    ->update([
                        'status' => '00',
                        'callback_response' => json_encode($request->all()),
                    ]);

                // Get all related PembayaranSiswa entries
                $pembayarans = PembayaranSiswa::where('merchant_order_id', $merchantOrderId)->orWhereNull('merchant_order_id')->get();

                foreach ($pembayarans as $pembayaran) {
                    if ($pembayaran->status == 0) { // Only update if not already processed
                        // Handle cicilan payments
                        $hasCicilan = PembayaranSiswaCicilan::where('pembayaran_siswa_id', $pembayaran->id)->exists();

                        if ($hasCicilan) {
                            $totalCicilan = PembayaranSiswaCicilan::where('pembayaran_siswa_id', $pembayaran->id)
                                ->sum('nominal_cicilan');

                            if ($totalCicilan >= $pembayaran->nominal) {
                                $pembayaran->status = 1; // Update to success
                                $pembayaran->save();
                            }
                        } else {
                            // If no cicilan, it's a full payment
                            $pembayaran->status = 1; // Update to success
                            $pembayaran->save();
                        }
                    }
                }

                DB::commit();

                return response('success', 200);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::emergency(json_encode($e));

                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return response('failure', 500);
    }

    public function getRiwayat(Request $request)
    {
        $siswa = auth()->user()->siswa;

        // Get Pembayaran where it matches kelas_id or siswa_id
        $pembayaranList = Pembayaran::where('kelas_id', $siswa->kelas->id)
            ->orWhere('siswa_id', $siswa->id)
            ->get();

        // Filter and map the data as requested
        $riwayat = $pembayaranList->map(function ($pembayaran) {
            // Get the first pembayaran_siswa with status 1
            $pembayaranSiswa = $pembayaran->pembayaran_siswa()->first();

            if (! ($pembayaranSiswa)) {
                return [
                    'kode_transaksi' => null,
                    'nominal' => $pembayaran->nominal,
                    'lunas' => false,
                    'tanggal_pembayaran' => $pembayaran->pembayaran_kategori->tanggal,
                ];
            }

            if ($pembayaranSiswa->merchant_order_id === null) {

                return [
                    'id' => $pembayaranSiswa->id,
                    'kode_transaksi' => null,
                    'nominal' => $pembayaran->nominal,
                    'lunas' => 'Belum Lunas',
                    'tanggal_pembayaran' => $pembayaranSiswa->updated_at,
                    'cicilan' => PembayaranSiswaCicilan::where('merchant_order_id', 'LIKE', "TFKC-" . $pembayaranSiswa->id . "-%")->with('pembayaran_duitku')->get(),
                ];
                
            }

            $pembayaranDuitku = PembayaranDuitku::where('merchant_order_id', $pembayaranSiswa->merchant_order_id)
                ->first();

            return [
                'kode_transaksi' => $pembayaranSiswa->merchant_order_id,
                'nominal' => $pembayaran->nominal,
                'lunas' => $pembayaranSiswa->status,
                'tanggal_pembayaran' => $pembayaranSiswa->updated_at,
                'duitku' => [
                    'request' => $pembayaranDuitku ? json_decode($pembayaranDuitku->transaction_response) : null,
                    'result' => $pembayaranDuitku ? json_decode($pembayaranDuitku->callback_response) : null,
                ],
            ];
        })->filter()->values();  // Filter out null entries and reset the array keys

        return response()->json([
            'message' => 'Riwayat pembayaran berhasil didapatkan',
            'data' => $riwayat,
        ]);
    }

    public function requestTransaksi(Request $request)
    {
        $validate = $request->validate([
            'payment_method' => ['string', 'required'],
            'return_url' => ['string', 'nullable'],
            'id' => ['integer', 'exists:pembayaran,id'],
            'cicil' => ['boolean', 'required'],
            'nominal' => ['required_if:cicil,true'],
        ]);

        DB::beginTransaction();

        try {
            $siswa = auth()->user()->siswa;

            $pembayaran = Pembayaran::with('pembayaran_kategori')->findOrFail($validate['id']);
            $merchantOrderId = null;
            $pesC = null;
            $pembayaranSiswa = null;
            if ($validate['cicil']) {
                // Check if an installment payment already exists
                $existingPembayaranSiswa = PembayaranSiswa::where('siswa_id', $siswa->id)
                    ->where('pembayaran_id', $pembayaran->id)
                    ->where('status', 0)
                    ->first();
                if ($existingPembayaranSiswa) {
                    // Update existing PembayaranSiswa for installments
                    $merchantOrderId = 'TFKC-' . $existingPembayaranSiswa->id . '-' . time();

                    // Store the installment (cicilan) details
                    $pesC = PembayaranSiswaCicilan::create([
                        'pembayaran_siswa_id' => $existingPembayaranSiswa->id,
                        'nominal_cicilan' => $validate['nominal'],
                        'merchant_order_id' => null,
                    ]);
                } else {
                    // Create a new PembayaranSiswa entry for installment
                    $pes = PembayaranSiswa::create([
                        'siswa_id' => $siswa->id,
                        'pembayaran_id' => $pembayaran->id,
                        'nominal' => $pembayaran->nominal,
                        'merchant_order_id' => null,  // Placeholder
                        'status' => 0,
                    ]);


                    // Generate merchantOrderId for cicilan
                    $merchantOrderId = 'TFKC-' . $pes->id . '-' . time();

                    // Store the installment (cicilan) details
                    $pesC = PembayaranSiswaCicilan::create([
                        'pembayaran_siswa_id' => $pes->id,
                        'nominal_cicilan' => $validate['nominal'],
                        'merchant_order_id' => $merchantOrderId,
                    ]);
                }

                // For cicil, use the installment nominal and name
                $nominal = $validate['nominal'];
                $name = 'Cicilan ' . $pembayaran->pembayaran_kategori->nama;
            } else {
                // Generate merchantOrderId for full payment
                $merchantOrderId = 'TFKT-0-' . time();

                // Create PembayaranSiswa entry for full payment
                $pembayaranSiswa =  PembayaranSiswa::create([
                    'siswa_id' => $siswa->id,
                    'pembayaran_id' => $pembayaran->id,
                    'nominal' => $pembayaran->nominal,
                    'merchant_order_id' => null,
                    'status' => 0,
                ]);

                // For full payment, use the full nominal and name
                $nominal = $pembayaran->nominal;
                $name = $pembayaran->pembayaran_kategori->nama;
            }

            $data = [
                'merchantOrderId' => $merchantOrderId,
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

            // Request transaction from Duitku
            $hasil = $this->duitku->requestTransaction($data);

            // Update PembayaranDuitku with the transaction result
            PembayaranDuitku::create([
                'merchant_order_id' => $merchantOrderId,
                'reference' => $hasil['reference'],
                'payment_method' => $validate['payment_method'],
                'transaction_response' => json_encode($hasil),
                'status' => 00,
            ]);
            if (!($validate['cicil'])) {
                $pembayaranSiswa->merchant_order_id = $merchantOrderId;
                $pembayaranSiswa->update();
            } else {
                $pesC->merchant_order_id = $merchantOrderId;
                $pesC->update();
            }



            DB::commit();

            return new PembayaranSiswaResource(true, 'Permintaan bayar berhasil dilakukan!', $hasil);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
