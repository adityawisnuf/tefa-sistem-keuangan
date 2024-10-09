<?php

namespace App\Http\Controllers;

use App\Models\KantinTransaksi;
use App\Models\LaundryTransaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class KepsekLaporanController extends Controller
{
    public function getKantinTransaksi(Request $request)
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'nama_usaha' => ['nullable', 'string'],
        ]);

        $startDate = $validated['tanggal_awal'] ?? null;
        $endDate = $validated['tanggal_akhir'] ?? null;
        $nama_usaha = $validated['nama_usaha'] ?? null;
        $perPage = $validated['per_page'] ?? 10;

        $transaksi = KantinTransaksi
            ::select('id', 'siswa_id', 'usaha_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
            ->with([
                'kantin_transaksi_detail:id,kantin_produk_id,kantin_transaksi_id,jumlah,harga',
                'kantin_transaksi_detail.kantin_produk:id,nama_produk,foto_produk,deskripsi',
                'usaha:id,nama_usaha',
                'siswa:id,nama_depan,nama_belakang'
            ])
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_selesai', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            })
            ->when($nama_usaha, function ($query) use ($nama_usaha) {
                $query->whereRelation('usaha', 'nama_usaha', 'like', "%$nama_usaha%");
            })
            ->whereIn('status', ['selesai', 'dibatalkan'])
            ->paginate($perPage);

        $transaksi->getCollection()->transform(function ($transaksi) {
            return array_merge(
                collect($transaksi)->forget(['siswa', 'usaha'])->toArray(),
                [
                    'nama_usaha' => $transaksi->usaha->nama_usaha,
                    'nama_siswa' => $transaksi->siswa->nama_siswa
                ],
            );
        });

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function getLaundryTransaksi(Request $request)
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'nama_usaha' => ['nullable', 'string'],
        ]);

        $startDate = $validated['tanggal_awal'] ?? null;
        $endDate = $validated['tanggal_akhir'] ?? null;
        $nama_usaha = $validated['nama_usaha'] ?? null;
        $perPage = $validated['per_page'] ?? 10;

        $transaksi = LaundryTransaksi
            ::select('id', 'siswa_id', 'usaha_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
            ->with([
                'laundry_transaksi_detail:id,laundry_layanan_id,laundry_transaksi_id,jumlah,harga',
                'laundry_transaksi_detail.laundry_layanan:id,nama_layanan,foto_layanan,deskripsi',
                'usaha:id,nama_usaha',
                'siswa:id,nama_depan,nama_belakang'
            ])
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_selesai', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            })
            ->when($nama_usaha, function ($query) use ($nama_usaha) {
                $query->whereRelation('usaha', 'nama_usaha', 'like', "%$nama_usaha%");
            })
            ->whereIn('status', ['selesai', 'dibatalkan'])
            ->paginate($perPage);

        $transaksi->getCollection()->transform(function ($transaksi) {
            return array_merge(
                collect($transaksi)->forget(['siswa', 'usaha'])->toArray(),
                [
                    'nama_usaha' => $transaksi->usaha->nama_usaha,
                    'nama_siswa' => $transaksi->siswa->nama_siswa
                ],
            );
        });

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function getDetailKantinTransaksi(Request $request, KantinTransaksi $transaksi)
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $data = $transaksi
            ->kantin_transaksi_detail()
            ->select('id', 'kantin_produk_id', 'jumlah', 'harga')
            ->with('kantin_produk:id,nama_produk,foto_produk,deskripsi,harga_jual,stok')
            ->paginate($perPage);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }
    public function getDetailLaundryTransaksi(Request $request, LaundryTransaksi $transaksi)
    {
        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $perPage = $validated['per_page'] ?? 10;

        $data = $transaksi
            ->laundry_transaksi_detail()
            ->select('id', 'laundry_layanan_id', 'jumlah', 'harga')
            ->with('laundry_layanan:id,nama_layanan,foto_layanan,deskripsi,harga,tipe,satuan')
            ->paginate($perPage);

        return response()->json(['data' => $data], Response::HTTP_OK);
    }
}