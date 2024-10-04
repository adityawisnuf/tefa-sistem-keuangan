<?php

namespace App\Http\Controllers;

use App\Http\Requests\SiswaLaundryRequest;
use App\Http\Services\SocketIOService;
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

    public function getLayananTransaksi()
    {
        $validator = Validator::make(request()->all(), [
            'per_page' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', 'in:aktif,selesai']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $siswa = Auth::user()->siswa;
        $perPage = request()->input('per_page', 10);
        $status = request()->input('status', 'aktif');

        try {
            $riwayat = $siswa->laundry_transaksi()
            ->select('id', 'usaha_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
            ->with(
                'usaha:id,nama_usaha', 
                'laundry_transaksi_detail:id,laundry_transaksi_id,laundry_layanan_id,jumlah,harga',
                'laundry_transaksi_detail.laundry_layanan:id,nama_layanan,foto_layanan,deskripsi,harga'
            )
            ->when($status == 'aktif', function ($query) {
                $query->whereIn('status', ['pending', 'proses', 'siap_diambil']);
            })
            ->when($status == 'selesai', function ($query) {
                $query->whereIn('status', ['selesai', 'dibatalkan']);
            })
            ->paginate($perPage);

        $riwayat->getCollection()->transform(function ($riwayat) {
            return array_merge(
                collect($riwayat)->forget(['usaha', 'laundry_transaksi_detail'])->toArray(),
                $riwayat->usaha->toArray(),
                ['laundry_transaksi_detail' => $riwayat->laundry_transaksi_detail],
            );
        });

            return response()->json(['data' => $riwayat], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getLayananRiwayat: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data riwayat transaksi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function createLayananTransaksi(SiswaLaundryRequest $request, SocketIOService $socketIOService)
    {
        $siswa = Auth::user()->siswa()->with('siswa_wallet')->firstOrFail();
        $fields = $request->validated();

        try {
            $usaha = LaundryLayanan::find($fields['detail_pesanan'][0]['laundry_layanan_id'])->usaha;
            $siswaWallet = $siswa->siswa_wallet;

            // periksa saldo sebelum melanjutkan transaksi
            $totalHarga = 0;
            foreach ($fields['detail_pesanan'] as $layananDetail) {
                $layanan = $usaha->laundry_layanan()->findOrFail($layananDetail['laundry_layanan_id']);
                $totalHarga += $layanan->harga * $layananDetail['jumlah'];
            }

            if ($siswaWallet->nominal < $totalHarga) {
                return response()->json(['error' => 'Saldo tidak mencukupi untuk transaksi ini.'], Response::HTTP_BAD_REQUEST);
            }

            $fields['siswa_id'] = $siswa->id;
            $fields['usaha_id'] = $usaha->id;

            DB::beginTransaction();
            $laundryTransaksi = LaundryTransaksi::create($fields);

            foreach ($fields['detail_pesanan'] as $layananDetail) {
                $layanan = $usaha->laundry_layanan()->findOrFail($layananDetail['laundry_layanan_id']);
                $qty = $layananDetail['jumlah'];

                LaundryTransaksiDetail::create([
                    'laundry_layanan_id' => $layanan->id,
                    'laundry_transaksi_id' => $laundryTransaksi->id,
                    'jumlah' => $qty,
                    'harga' => $layanan->harga,
                ]);
            }

            // update saldo secara atomik
            $usaha->increment('saldo', $totalHarga);

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

            $socketIOService->remindFetch($usaha->user->id);

            return response()->json(['data' => $laundryTransaksi], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('createLayananTransaksi: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat membuat data laundry layanan.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
