<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SiswaWalletController extends Controller
{
    public function getSaldo()
    {
        $siswaWallet = Auth::user()->siswa->siswa_wallet;

        $pemasukan = $siswaWallet
            ->siswa_wallet_riwayat()
            ->whereBetween('tanggal_riwayat', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])
            ->where('tipe_transaksi', 'pemasukan')
            ->sum('nominal');

        $pengeluaran = $siswaWallet
            ->siswa_wallet_riwayat()
            ->whereBetween('tanggal_riwayat', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])
            ->where('tipe_transaksi', 'pengeluaran')
            ->sum('nominal');

        return response()->json([
            'data' => [
                'saldo_siswa' => $siswaWallet->nominal,
                'total_pemasukan' => $pemasukan,
                'total_pengeluaran' => $pengeluaran,
            ]
        ], Response::HTTP_OK);
    }



    public function getRiwayat(Request $request)
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1'],
            'bulan' => ['nullable', 'integer', 'min:1', 'max:12'],
            'tipe_transaksi' => ['nullable', 'string', 'in:pemasukan,pengeluaran']
        ]);

        $siswaWallet = Auth::user()->siswa->siswa_wallet;
        $bulan = $validated['bulan'] ?? Carbon::now()->month;
        $tipeTransaksi = $validated['tipe_transaksi'] ?? null;
        $perPage = $validated['per_page'] ?? 10;

        $siswaWalletRiwayat = $siswaWallet
            ->siswa_wallet_riwayat()
            ->select('id', 'tipe_transaksi', 'nominal', 'tanggal_riwayat')
            ->whereMonth('tanggal_riwayat', $bulan)
            ->when($tipeTransaksi, function ($query) use ($tipeTransaksi) {
                $query->where('tipe_transaksi', $tipeTransaksi);
            })
            ->latest()
            ->paginate($perPage);

        return response()->json(['data' => $siswaWalletRiwayat], Response::HTTP_OK);
    }
}