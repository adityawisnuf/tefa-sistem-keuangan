<?php

namespace App\Http\Controllers;

use App\Models\KantinTransaksi;
use App\Models\LaundryTransaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class KepsekLaporanController extends Controller
{
    public function getKantinTransaksi()
    {
        $validator = Validator::make(request()->all(), [
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'nama_usaha' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $startDate = request('tanggal_awal');
        $endDate = request('tanggal_akhir');
        $nama_usaha = request('nama_usaha');
        $perPage = request('per_page', 10);

        try {
            $transaksi = KantinTransaksi::with([
                'kantin_transaksi_detail.kantin_produk:id,nama_produk,foto_produk,deskripsi',
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

            $transaksi->getCollection()->transform(function ($transaksi) {
                return [
                    'id' => $transaksi->id,
                    'nama_siswa' => "{$transaksi->siswa->nama_depan} {$transaksi->siswa->nama_belakang}",
                    'nama_usaha' => $transaksi->usaha->nama_usaha,
                    'status' => $transaksi->status,
                    'tanggal_pemesanan' => $transaksi->tanggal_pemesanan,
                    'tanggal_selesai' => $transaksi->tanggal_selesai,
                    'kantin_transaksi_detail' => $transaksi->kantin_transaksi_detail
                ];
            });

            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('getUsahaTransaksi: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data transaksi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getLaundryTransaksi()
    {
        $validator = Validator::make(request()->all(), [
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'nama_usaha' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $startDate = request('tanggal_awal');
        $endDate = request('tanggal_akhir');
        $nama_usaha = request('nama_usaha');
        $perPage = request('per_page', 10);

        try {
            $transaksi = LaundryTransaksi::with([
                'laundry_transaksi_detail.laundry_layanan:id,nama_layanan,foto_layanan,deskripsi',
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

            $transaksi->getCollection()->transform(function ($transaksi) {
                return [
                    'id' => $transaksi->id,
                    'nama_siswa' => "{$transaksi->siswa->nama_depan} {$transaksi->siswa->nama_belakang}",
                    'nama_usaha' => $transaksi->usaha->nama_usaha,
                    'status' => $transaksi->status,
                    'tanggal_pemesanan' => $transaksi->tanggal_pemesanan,
                    'tanggal_selesai' => $transaksi->tanggal_selesai,
                    'laundry_transaksi_detail' => $transaksi->laundry_transaksi_detail
                ];
            });

            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('getUsahaTransaksi: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data transaksi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDetailKantinTransaksi(KantinTransaksi $transaksi)
    {
        $validator = Validator::make(request()->all(), [
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $perPage = request('per_page', 10);

        try {
            $data = $transaksi
                    ->kantin_transaksi_detail()
                    ->with(['kantin_produk'])
                    ->paginate($perPage);


            return response()->json(['data' => $data], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('getDetailUsahaTransaksi: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data transaksi.' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function getDetailLaundryTransaksi(LaundryTransaksi $transaksi)
    {
        $validator = Validator::make(request()->all(), [
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $perPage = request('per_page', 10);

        try {
            $data = $transaksi
                    ->laundry_transaksi_detail()
                    ->with(['laundry_layanan'])
                    ->paginate($perPage);


            return response()->json(['data' => $data], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('getDetailLaundryTransaksi: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data transaksi.' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}