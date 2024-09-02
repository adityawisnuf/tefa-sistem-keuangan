<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiswaLaundryRequest;
use App\Models\LaundryLayanan;
use App\Models\LaundryTransaksi;
use App\Models\LaundryTransaksiDetail;
use App\Models\SiswaWalletRiwayat;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class SiswaLaundryController extends Controller
{
    public function getLayanan()
    {
        $validator = Validator::make(request()->all(), [
            'per_page' => ['nullable', 'integer', 'min:1'],
            'tipe' => ['nullable', 'string', 'in:kiloan,satuan'],
            'nama_layanan' => ['nullable', 'string']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $perPage = request()->input('per_page', 10);
        $tipe = request('tipe', 'kiloan');
        $namaLayanan = request('nama_layanan');

        try {
            $items = LaundryLayanan::where('status', 'aktif')
                ->where('tipe', $tipe)
                ->when($namaLayanan, function ($query) use ($namaLayanan) {
                    $query->where('nama_layanan', 'like', "%$namaLayanan%");
                })
                ->latest()
                ->paginate($perPage);

            return response()->json(['data' => $items], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getLayanan: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data layanan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getLayananDetail($id)
    {
        try {
            $layanan = LaundryLayanan::findOrFail($id);
            return response()->json(['data' => $layanan], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getLayananDetail: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data layanan detail.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getLayananRiwayat()
    {

        $validator = Validator::make(request()->all(), [
            'per_page' => ['nullable', 'integer', 'min:1'],
            'siswa' => ['nullable', 'integer', 'min:1']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }
        
        $siswa = Auth::user()->siswa()->first();
        $perPage = request('per_page', 10);

        try {
            $riwayat = $siswa->laundry_transaksi()
            ->with(['usaha', 'laundry_transaksi_detail.laundry_layanan'])
            ->whereIn('status',['dibatalkan','selesai'])
            ->paginate($perPage);

            $riwayat->getCollection()->transform(function ($riwayat) {
                return [
                    'id' => $riwayat->id,
                    'nama_usaha' => $riwayat->usaha->nama_usaha,
                    'jumlah_layanan' => count($riwayat->laundry_transaksi_detail),
                    'harga_total' => array_reduce($riwayat->laundry_transaksi_detail->toArray(), function($scary, $item) {
                        return $scary += $item['harga_total']; //horror sikit
                    }),
                    'status' => $riwayat->status,
                    'tanggal_pemesanan' => $riwayat->tanggal_pemesanan,
                    'tanggal_selesai' => $riwayat->tanggal_selesai,
                ];
            });

            return response()->json(['data' => $riwayat], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getLayananRiwayat: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data riwayat transaksi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getLayananTransaksi()
    {
        $siswa = Auth::user()->siswa()->first();
        $perPage = request('per_page', 10);

        try {
            $riwayat = $siswa->laundry_transaksi()
            ->with(['usaha', 'laundry_transaksi_detail.laundry_layanan'])
            ->whereIn('status',['proses','siap_diambil'])
            ->paginate($perPage);

            $riwayat->getCollection()->transform(function ($riwayat) {
                return [
                    'id' => $riwayat->id,
                    'nama_usaha' => $riwayat->usaha->nama_usaha,
                    'jumlah_layanan' => count($riwayat->laundry_transaksi_detail),
                    'harga_total' => array_reduce($riwayat->laundry_transaksi_detail->toArray(), function($scary, $item) {
                        return $scary += $item['harga_total']; //horror sikit
                    }),
                    'status' => $riwayat->status,
                    'tanggal_pemesanan' => $riwayat->tanggal_pemesanan,
                    'tanggal_selesai' => $riwayat->tanggal_selesai,
                ];
            });

            return response()->json(['data' => $riwayat], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getLayananRiwayat: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data riwayat transaksi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createLayananTransaksi(SiswaLaundryRequest $request)
    {
        $siswa = Auth::user()->siswa()->with('siswa_wallet')->firstOrFail();
        $fields = $request->validated();

        try{
            $usaha = LaundryLayanan::find($fields['detail_pesanan'][0]['laundry_layanan_id'])->usaha;
            $siswaWallet = $siswa->siswa_wallet;

            $fields['siswa_id'] = $siswa->id;
            $fields['usaha_id'] = $usaha->id;

            DB::beginTransaction();
            $laundryTransaksi = LaundryTransaksi::create($fields);
            $totalHarga = 0;

            foreach ($fields['detail_pesanan'] as $layananDetail) {
                $layanan = $usaha->laundry_layanan()->findOrFail($layananDetail['laundry_layanan_id']);
                $qty = $layananDetail['jumlah'];


                LaundryTransaksiDetail::create([
                    'laundry_layanan_id' => $layanan->id,
                    'laundry_transaksi_id' => $laundryTransaksi->id,
                    'jumlah' => $qty,
                    'harga' => $layanan->harga
                ]);

                $totalHarga += $layanan->harga * $layananDetail['jumlah'];
            }


            if ($siswaWallet->nominal < $totalHarga) {
                return response()->json(['error' => 'Saldo tidak mencukupi untuk transaksi ini.'], Response::HTTP_BAD_REQUEST);
            }

            $usaha->update([
                'saldo' => $usaha->saldo + $totalHarga,
            ]);

            $siswaWallet->update([
                'nominal' => $siswaWallet->nominal - $totalHarga,
            ]);

            SiswaWalletRiwayat::create([
                'siswa_wallet_id' => $siswaWallet->id,
                'merchant_order_id' => null,
                'tipe_transaksi' => 'pengeluaran',
                'nominal' => $totalHarga,
            ]);
            DB::commit();

            $client = new Client();
            $client->post(env('WEBSOCKET_URL') . '/siswa-transaksi-laundry');

            return response()->json(['data' => $laundryTransaksi], Response::HTTP_CREATED);
        }  catch (\Exception $e) {
            DB::rollBack();
            Log::error('createLayananTransaksi: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat membuat data laundry layanan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
