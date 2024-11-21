<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            'bulan' => ['nullable', 'integer', 'min:1', 'max:12']
        ]);

        $bulan = $validated['bulan'] ?? Carbon::now()->month;

        $siswa = $orangtua->siswa()->findOrFail($id);
        $siswaWallet = $siswa->siswa_wallet;

        $pemasukan = $siswaWallet
        ->siswa_wallet_riwayat()
        ->whereMonth('tanggal_riwayat', $bulan)
        ->where('tipe_transaksi', 'pemasukan')
        ->sum('nominal');

        $pengeluaran = $siswaWallet
        ->siswa_wallet_riwayat()
        ->whereMonth('tanggal_riwayat', $bulan)
        ->where('tipe_transaksi', 'pengeluaran')
        ->sum('nominal');

        return response()->json([
            'data' => [
                'id' => $siswa->id,
                'nama_siswa' => $siswa->nama_siswa,
                'saldo_siswa' => $siswaWallet->nominal,
                'total_pemasukan' => $pemasukan,
                'total_pengeluaran' => $pengeluaran,
            ]
        ], Response::HTTP_OK);
    }

    public function getRiwayatKantinSiswa(Request $request, $id)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $orangTua = Auth::user()->orangtua;
        $perPage = $validated['per_page'] ?? 10;

        $riwayat = $orangTua
            ->siswa()
            ->findOrFail($id)
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

        $riwayat = $orangTua
            ->siswa()
            ->findOrFail($id)
            ->laundry_transaksi()
            ->select('id', 'siswa_id', 'status', 'tanggal_pemesanan', 'tanggal_selesai')
            ->with(
                'laundry_transaksi_detail:id,laundry_transaksi_id,laundry_layanan_id,jumlah,harga',
                'laundry_transaksi_detail.laundry_layanan:id,nama_layanan,foto_layanan,harga'
            )
            ->whereIn('status', ['dibatalkan', 'selesai'])
            ->paginate($perPage);

        return response()->json(['data' => $riwayat], Response::HTTP_OK);
    }

}