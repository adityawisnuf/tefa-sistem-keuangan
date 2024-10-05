<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class OrangTuaController extends Controller
{
    public function getSiswa()
    {
        $orangtua = Auth::user()->orangtua;
        $siswa = $orangtua->siswa()->select('id', 'nama_depan', 'nama_belakang')->get();

        return response()->json(['data' => $siswa], Response::HTTP_OK);
    }

    public function getRiwayatWalletSiswa(Request $request, $id)
    {
        $orangtua = Auth::user()->orangtua;

        $validated = $request->validate([
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
        ]);

        $startDate = $validated['tanggal_awal'] ?? null;
        $endDate = $validated['tanggal_akhir'] ?? null;

        $siswa = $orangtua->siswa()
            ->with([
                'siswa_wallet',
                'siswa_wallet.siswa_wallet_riwayat' => function ($query) use ($startDate, $endDate) {
                    $query
                        ->select(
                            'siswa_wallet_id',
                            DB::raw('SUM(CASE WHEN tipe_transaksi = "pemasukan" THEN nominal ELSE 0 END) as total_pemasukan'),
                            DB::raw('SUM(CASE WHEN tipe_transaksi = "pengeluaran" THEN nominal ELSE 0 END) as total_pengeluaran'),
                        )
                        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                            $query->whereBetween('tanggal_riwayat', [
                                Carbon::parse($startDate)->startOfDay(),
                                Carbon::parse($endDate)->endOfDay()
                            ]);
                        })
                        ->groupBy('siswa_wallet_id');
                }
            ])
            ->findOrFail($id);

        $data = [
            'id' => $siswa->id,
            'nama_siswa' => $siswa->nama_siswa,
            'saldo_siswa' => $siswa->siswa_wallet->nominal,
            'total_pemasukan' => $siswa->siswa_wallet->siswa_wallet_riwayat->first()->total_pemasukan ?? 0,
            'total_pengeluaran' => $siswa->siswa_wallet->siswa_wallet_riwayat->first()->total_pengeluaran ?? 0,
        ];

        return response()->json(['data' => $data], Response::HTTP_OK);
    }

    public function getRiwayatKantinSiswa(Request $request, $id)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $orangTua = Auth::user()->orangtua;
        $perPage = $validated['per_page'] ?? 10;

        $siswa = $orangTua->siswa()->findOrFail($id);

        $riwayat = $siswa
            ->kantin_transaksi()
            ->select('id', 'siswa_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
            ->with(
                'kantin_transaksi_detail:id,kantin_transaksi_id,kantin_produk_id,jumlah,harga',
                'kantin_transaksi_detail.kantin_produk:id,nama_produk,foto_produk,harga_jual'
            )
            ->whereIn('status', ['dibatalkan', 'selesai'])
            ->paginate($perPage);

        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }

    public function getRiwayatLaundrySiswa(Request $request, $id)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $orangTua = Auth::user()->orangtua;
        $perPage = $validated['per_page'] ?? 10;

        $siswa = $orangTua->siswa()->findOrFail($id);

        $riwayat = $siswa->laundry_transaksi()
            ->select('id', 'siswa_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
            ->with(
                'laundry_transaksi_detail:id,laundry_layanan_id,laundry_transaksi_id,jumlah,harga',
                'laundry_transaksi_detail.laundry_layanan:id,nama_layanan,foto_layanan,harga'
            )
            ->whereIn('status', ['dibatalkan', 'selesai'])
            ->paginate($perPage);

        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }
}