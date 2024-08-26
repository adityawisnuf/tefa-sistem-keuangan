<?php

namespace App\Http\Controllers;

use App\Models\KantinTransaksi;
use App\Models\LaundryTransaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class BendaharaLaporanController extends Controller
{
    public function getUsahaTransaksi()
    {
        $validator = Validator::make(request()->all(), [
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'role' => ['nullable', 'in:Kantin,Laundry'],
            'nama_usaha' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $startDate = request('tanggal_awal');
        $endDate = request('tanggal_akhir');
        $nama_usaha = request('nama_usaha');
        $role = request('role', 'Kantin');
        $perPage = request('per_page', 10);

        $model = $role == 'Kantin' ? new KantinTransaksi : new LaundryTransaksi;

        try {
            $transaksi = $model->with([
                $role == 'Kantin'
                ? 'kantin_transaksi_detail.kantin_produk:id,nama_produk,foto_produk,deskripsi'
                : 'laundry_transaksi_detail.laundry_layanan:id,nama_layanan,foto_layanan,deskripsi',
                'usaha:id,nama_usaha',
                'siswa:id,nama_depan,nama_belakang'
            ])
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('tanggal_selesai', [
                        Carbon::parse($startDate)->startOfDay(),
                        Carbon::parse($endDate)->endOfDay()
                    ]);
                }, function ($query) {
                    $query->whereBetween('tanggal_selesai', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth()
                    ]);
                })
                ->whereIn('status', ['selesai', 'dibatalkan'])
                ->when($nama_usaha, function ($query) use ($nama_usaha) {
                    $query->whereRelation('usaha', 'nama_usaha', 'like', "%$nama_usaha%");
                })
                ->paginate($perPage);

            $transaksi->getCollection()->transform(function ($transaksi) use ($role) {
                return [
                    'id' => $transaksi->id,
                    'nama_siswa' => "{$transaksi->siswa->nama_depan} {$transaksi->siswa->nama_belakang}",
                    'nama_usaha' => $transaksi->usaha->nama_usaha,
                    'status' => $transaksi->status,
                    'tanggal_pemesanan' => $transaksi->tanggal_pemesanan,
                    'tanggal_selesai' => $transaksi->tanggal_selesai,
                    'transaksi_detail' => $role == 'Kantin'
                        ? $transaksi->kantin_transaksi_detail
                        : $transaksi->laundry_transaksi_detail
                ];
            });

            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data transaksi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
            // return response()->json(['error' => 'Terjadi kesalahan saat mengambil data transaksi: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
