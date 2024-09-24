<?php

namespace App\Http\Controllers;

use App\Models\KantinTransaksi;
use App\Models\LaundryTransaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class BendaharaLaporanController extends Controller
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

        $perPage = request('per_page', 10);
        $startDate = request('tanggal_awal');
        $endDate = request('tanggal_akhir');
        $nama_usaha = request('nama_usaha');

        try {
            $transaksi = KantinTransaksi
                ::select('id', 'usaha_id', 'siswa_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
                ->with(
                    'kantin_transaksi_detail:id,kantin_transaksi_id,kantin_produk_id,jumlah,harga',
                    'kantin_transaksi_detail.kantin_produk:id,nama_produk,foto_produk,deskripsi',
                    'usaha:id,nama_usaha',
                    'siswa:id,nama_depan,nama_belakang'
                )
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
                ->when($nama_usaha, function ($query) use ($nama_usaha) {
                    $query->whereRelation('usaha', 'nama_usaha', 'like', "%$nama_usaha%");
                })
                ->whereIn('status', ['selesai', 'dibatalkan'])
                ->paginate($perPage);

            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('getUsahaTransaksi: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data transaksi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDetailKantinTransaksi($id)
    {
        try {
            $data = KantinTransaksi
                ::select('id', 'usaha_id', 'siswa_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
                ->with(
                    'kantin_transaksi_detail:id,kantin_transaksi_id,kantin_produk_id,jumlah,harga',
                    'kantin_transaksi_detail.kantin_produk:id,nama_produk,foto_produk,deskripsi',
                    'usaha:id,nama_usaha',
                    'siswa:id,nama_depan,nama_belakang'
                )
                ->findOrFail($id);

            return response()->json(['data' => $data], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Log::error('getUsahaTransaksi: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data transaksi: ' . $e], Response::HTTP_INTERNAL_SERVER_ERROR);
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

        $perPage = request('per_page', 10);
        $startDate = request('tanggal_awal');
        $endDate = request('tanggal_akhir');
        $nama_usaha = request('nama_usaha');

        try {
            $transaksi = LaundryTransaksi
                ::select('id', 'usaha_id', 'siswa_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
                ->with(
                    'laundry_transaksi_detail:id,laundry_transaksi_id,laundry_layanan_id,jumlah,harga',
                    'laundry_transaksi_detail.laundry_layanan:id,nama_layanan,foto_layanan,deskripsi',
                    'usaha:id,nama_usaha',
                    'siswa:id,nama_depan,nama_belakang'
                )
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

            return response()->json(['data' => $transaksi], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('getUsahaTransaksi: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data transaksi.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getDetailLaundryTransaksi($id)
    {
        try {
            $data = LaundryTransaksi
                ::select('id', 'usaha_id', 'siswa_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
                ->with(
                    'laundry_transaksi_detail:id,laundry_transaksi_id,laundry_layanan_id,jumlah,harga',
                    'laundry_transaksi_detail.laundry_layanan:id,nama_layanan,foto_layanan,deskripsi',
                    'usaha:id,nama_usaha',
                    'siswa:id,nama_depan,nama_belakang'
                )
                ->findOrFail($id);

            return response()->json(['data' => $data], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Log::error('getUsahaTransaksi: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data transaksi: ' . $e], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}