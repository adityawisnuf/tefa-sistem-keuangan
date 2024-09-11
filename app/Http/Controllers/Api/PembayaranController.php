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


        $pembayaranKategori = PembayaranKategori::where('status', 1)
            ->whereHas('pembayaran', function ($query) use ($siswa) {
                $query->where('siswa_id', $siswa->id)
                    ->orWhere('kelas_id', $siswa->kelas_id);
            })
            ->where('jenis_pembayaran', 1)
            ->with(['pembayaran' => function ($query) use ($siswa) {
                $query->with(['pembayaran_siswa' => function ($query) use ($siswa) {
                    $query->where('siswa_id', $siswa->id);
                }, 'pembayaran_siswa.pembayaran_siswa_cicilan']);
            }])
            ->first();


        if (! $pembayaranKategori || ! $pembayaranKategori->pembayaran->first()) {
            return response()->json(['success' => false, 'message' => 'No pembayaran data found'], 404);
        }


        $pembayaranList = $pembayaranKategori->pembayaran
            ->map(function ($pembayaran) use ($pembayaranKategori, $siswa) {

                $pembayaranSiswa = $pembayaran->pembayaran_siswa()->first();


                $totalCicilan = 0;
                $duitkuTransactions = [];

                if ($pembayaranSiswa && $pembayaranSiswa->pembayaran_siswa_cicilan->count() > 0) {
                    $totalCicilan = $pembayaranSiswa->pembayaran_siswa_cicilan->sum('nominal_cicilan');
                    foreach ($pembayaranSiswa->pembayaran_siswa_cicilan as $value) {
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
                    'total_cicilan' => $totalCicilan,
                    'duitku' => $duitkuTransactions,
                ];
            })->filter(function ($pembayaran) {

                return $pembayaran['pembayaran_valid'] === false || $pembayaran['pembayaran_valid'] === 0;
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
        if (strtolower($request->header('User-Agent')) !== 'duitku callback agent') {
            return response('ANDA TELAH MELAKUKAN MANIPULASI DATA YANG MELANGGAR UU ITE YANG BERLAKU', 403);
        }

        Log::debug(json_encode($request->all()));
        $status = $this->duitku->callback($request->all());

        if ($status) {
            DB::beginTransaction();

            try {
                $merchantOrderId = $request->merchantOrderId;


                PembayaranDuitku::where('merchant_order_id', $merchantOrderId)
                    ->update([
                        'status' => '00',
                        'callback_response' => json_encode($request->all()),
                    ]);


                $pembayarans = PembayaranSiswa::where('merchant_order_id', $merchantOrderId)->orWhereNull('merchant_order_id')->get();

                foreach ($pembayarans as $pembayaran) {
                    if ($pembayaran->status == 0) {

                        $hasCicilan = PembayaranSiswaCicilan::where('pembayaran_siswa_id', $pembayaran->id)->exists();

                        if ($hasCicilan) {
                            $totalCicilan = PembayaranSiswaCicilan::where('pembayaran_siswa_id', $pembayaran->id)
                                ->sum('nominal_cicilan');

                            if ($totalCicilan >= $pembayaran->nominal) {
                                $pembayaran->status = 1;
                                $pembayaran->save();
                            }
                        } else {

                            $pembayaran->status = 1;
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
            ->select('pembayaran_siswa.*', 'pembayaran_duitku.*')
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

                $existingPembayaranSiswa = PembayaranSiswa::where('siswa_id', $siswa->id)
                    ->where('pembayaran_id', $pembayaran->id)
                    ->where('status', 0)
                    ->first();
                if ($existingPembayaranSiswa) {

                    $merchantOrderId = 'TFKC-' . $existingPembayaranSiswa->id . '-' . time();


                    $pesC = PembayaranSiswaCicilan::create([
                        'pembayaran_siswa_id' => $existingPembayaranSiswa->id,
                        'nominal_cicilan' => $validate['nominal'],
                        'merchant_order_id' => null,
                    ]);
                } else {

                    $pes = PembayaranSiswa::create([
                        'siswa_id' => $siswa->id,
                        'pembayaran_id' => $pembayaran->id,
                        'nominal' => $pembayaran->nominal,
                        'merchant_order_id' => null,
                        'status' => 0,
                    ]);



                    $merchantOrderId = 'TFKC-' . $pes->id . '-' . time();


                    $pesC = PembayaranSiswaCicilan::create([
                        'pembayaran_siswa_id' => $pes->id,
                        'nominal_cicilan' => $validate['nominal'],
                        'merchant_order_id' => $merchantOrderId,
                    ]);
                }


                $nominal = $validate['nominal'];
                $name = 'Cicilan ' . $pembayaran->pembayaran_kategori->nama;
            } else {

                $merchantOrderId = 'TFKT-0-' . time();


                $pembayaranSiswa =  PembayaranSiswa::create([
                    'siswa_id' => $siswa->id,
                    'pembayaran_id' => $pembayaran->id,
                    'nominal' => $pembayaran->nominal,
                    'merchant_order_id' => null,
                    'status' => 0,
                ]);


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


            $hasil = $this->duitku->requestTransaction($data);


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
    public function getCurrentYear(Request $request)
    {
        $siswa = auth()->user()->siswa;

        $pembayaranKategoris = PembayaranKategori::where('status', 1)
            ->whereHas('pembayaran', function ($query) use ($siswa) {
                $query->where('siswa_id', $siswa->id)
                    ->orWhere('kelas_id', $siswa->kelas_id);
            })
            ->where('jenis_pembayaran', 2)
            ->with(['pembayaran' => function ($query) use ($siswa) {
                $query->with(['pembayaran_siswa' => function ($query) use ($siswa) {
                    $query->where('siswa_id', $siswa->id);
                }, 'pembayaran_siswa.pembayaran_siswa_cicilan']);
            }])
            ->get();

        $pembayaranList = [];
        foreach ($pembayaranKategoris as $pembayaranKategori) {
            $pembayaran = $pembayaranKategori->pembayaran->first(); // Get the first pembayaran

            if ($pembayaran) {
                $pembayaranSiswa = $pembayaran->pembayaran_siswa()->first();

                $totalCicilan = 0;
                $duitkuTransactions = [];

                if ($pembayaranSiswa && $pembayaranSiswa->pembayaran_siswa_cicilan->count() > 0) {
                    $totalCicilan = $pembayaranSiswa->pembayaran_siswa_cicilan->sum('nominal_cicilan');
                    foreach ($pembayaranSiswa->pembayaran_siswa_cicilan as $value) {
                        $duitkuTransactions[] = $value->pembayaran_duitku;
                    }
                } elseif ($pembayaranSiswa) {
                    $duitkuTransactions = PembayaranDuitku::where('merchant_order_id', $pembayaranSiswa->merchant_order_id)->get();
                }

                $pembayaranList[] = [
                    'id' => $pembayaran->id,
                    'nama' => $pembayaranKategori->nama,
                    'tanggal_bayar' => $pembayaranKategori->tanggal_pembayaran,
                    'nominal' => $pembayaran->nominal,
                    'siswa_sudah_bayar' => $pembayaranSiswa ? true : false,
                    'pembayaran_valid' => $pembayaranSiswa ? $pembayaranSiswa->status : false,
                    'merchant_order_id' => $pembayaranSiswa ? $pembayaranSiswa->merchant_order_id : null,
                    'total_cicilan' => $totalCicilan,
                    'duitku' => $duitkuTransactions,
                ];
            }
        }

        $filteredPembayaranList = collect($pembayaranList)->filter(function ($pembayaran) {
            return $pembayaran['pembayaran_valid'] === false || $pembayaran['pembayaran_valid'] === 0;
        });

        return response()->json([
            'success' => true,
            'nama' => $siswa->nama_depan . ($siswa->nama_belakang ? ' ' . $siswa->nama_belakang : ''),
            'jurusan' => $siswa->kelas->jurusan,
            'kelas' => $siswa->kelas->kelas,
            'orang_tua' => $siswa->orangtua->nama,
            'message' => 'List Pembayaran Siswa',
            'data' => [
                'pembayaran' => $filteredPembayaranList,
            ],
        ]);
    }
}
