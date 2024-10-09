<?php

namespace App\Http\Controllers;

use App\Models\KantinTransaksi;
use App\Models\LaundryTransaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class BendaharaLaporanController extends Controller
{
    public function getKantinTransaksi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'nama_usaha' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $perPage = $request->input('per_page', 10);
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');
        $nama_usaha = $request->input('nama_usaha');

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

        $transaksi->getCollection()->transform(function ($transaksi) {
            return array_merge(
                collect($transaksi)->forget(['usaha', 'siswa'])->toArray(),
                collect($transaksi->usaha)->except('id')->toArray(),
                ['nama_siswa' => $transaksi->siswa->nama_siswa],
            );
        });

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function getDetailKantinTransaksi($id)
    {
        $transaksi = KantinTransaksi
            ::select('id', 'usaha_id', 'siswa_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
            ->with(
                'kantin_transaksi_detail:id,kantin_transaksi_id,kantin_produk_id,jumlah,harga',
                'kantin_transaksi_detail.kantin_produk:id,nama_produk,foto_produk,deskripsi',
                'usaha:id,nama_usaha',
                'siswa:id,nama_depan,nama_belakang'
            )
            ->findOrFail($id);

        $transaksi = array_merge(
            collect($transaksi)->forget(['usaha', 'siswa'])->toArray(),
            collect($transaksi->usaha)->except('id')->toArray(),
            ['nama_siswa' => $transaksi->siswa->nama_siswa],
        );

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function getLaundryTransaksi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'per_page' => ['nullable', 'integer', 'min:1'],
            'nama_usaha' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $perPage = $request->input('per_page', 10);
        $startDate = $request->input('tanggal_awal');
        $endDate = $request->input('tanggal_akhir');
        $nama_usaha = $request->input('nama_usaha');

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

        $transaksi->getCollection()->transform(function ($transaksi) {
            return array_merge(
                collect($transaksi)->forget(['usaha', 'siswa'])->toArray(),
                collect($transaksi->usaha)->except('id')->toArray(),
                ['nama_siswa' => $transaksi->siswa->nama_siswa],
            );
        });

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }

    public function getDetailLaundryTransaksi($id)
    {
        $transaksi = LaundryTransaksi
            ::select('id', 'usaha_id', 'siswa_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
            ->with(
                'laundry_transaksi_detail:id,laundry_transaksi_id,laundry_layanan_id,jumlah,harga',
                'laundry_transaksi_detail.laundry_layanan:id,nama_layanan,foto_layanan,deskripsi',
                'usaha:id,nama_usaha',
                'siswa:id,nama_depan,nama_belakang'
            )
            ->findOrFail($id);

        $transaksi = array_merge(
            collect($transaksi)->forget(['usaha', 'siswa'])->toArray(),
            collect($transaksi->usaha)->except('id')->toArray(),
            ['nama_siswa' => $transaksi->siswa->nama_siswa],
        );

        return response()->json(['data' => $transaksi], Response::HTTP_OK);
    }
}