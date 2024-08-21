<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SiswaWalletController extends Controller
{
    private $startOfMonth;
    private $endOfMonth;

    public function __construct()
    {
        $this->startOfMonth = Carbon::now()->startOfMonth();
        $this->endOfMonth = Carbon::now()->endOfMonth();
    }

    public function getSaldo()
    {
        $siswaWallet = Auth::user()->siswa->first()->siswa_wallet;
        $pemasukan = $siswaWallet->siswa_wallet_riwayat()->whereBetween('tanggal_riwayat', [$this->startOfMonth, $this->endOfMonth])->where('tipe_transaksi', 'pemasukan')->sum('nominal');
        $pengeluaran = $siswaWallet->siswa_wallet_riwayat()->whereBetween('tanggal_riwayat', [$this->startOfMonth, $this->endOfMonth])->where('tipe_transaksi', 'pengeluaran')->sum('nominal');
        return response()->json([
            'data' => [
                'saldo' => $siswaWallet->nominal,
                'pemasukan' => $pemasukan,
                'pengeluaran' => $pengeluaran
            ]
        ], Response::HTTP_OK);
    }

    public function getRiwayat()
    {
        $siswaWallet = Auth::user()->siswa->first()->siswa_wallet;
        $perPage = request()->input('per_page', 10);

        $tipeTransaksi = request()->input('tipe_transaksi');

        $bulan = request()->input('bulan', Carbon::now()->month);
        $tahun = request()->input('tahun', Carbon::now()->year);

        $startOfMonth = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endOfMonth = Carbon::create($tahun, $bulan, 1)->endOfMonth();

        $query = $siswaWallet->siswa_wallet_riwayat()
                    ->whereBetween('tanggal_riwayat', [$startOfMonth, $endOfMonth])
                    ->latest();

        if ($tipeTransaksi) {
            $query->where('tipe_transaksi', $tipeTransaksi);
        }

        $siswaWalletRiwayat = $query->paginate($perPage);

        return response()->json(['data' => $siswaWalletRiwayat], Response::HTTP_OK);
    }

}
