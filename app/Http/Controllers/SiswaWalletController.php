<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Number;
use Exception;
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
        try {
            $pemasukan = $siswaWallet->siswa_wallet_riwayat()->whereBetween('tanggal_riwayat', [$this->startOfMonth, $this->endOfMonth])->where('tipe_transaksi', 'pemasukan')->sum('nominal');
            $pengeluaran = $siswaWallet->siswa_wallet_riwayat()->whereBetween('tanggal_riwayat', [$this->startOfMonth, $this->endOfMonth])->where('tipe_transaksi', 'pengeluaran')->sum('nominal');

            return response()->json([
                'data' => [
                    'saldo_siswa' => 'Rp' . Number::format($siswaWallet->nominal, 0, 0, 'id-ID'),
                    'total_pemasukan' => 'Rp' . Number::format($pemasukan, 0, 0, 'id-ID'),
                    'total_pengeluaran' => 'Rp' . Number::format($pengeluaran, 0, 0, 'id-ID'),
                ]
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getSaldo: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data walet.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }



    public function getRiwayat()
    {
        $validator = Validator::make(request()->all(), [
            'per_page' => ['nullable', 'integer', 'min:1'],
            'tanggal_awal' => ['nullable', 'date'],
            'tanggal_akhir' => ['nullable', 'date', 'after_or_equal:tanggal_awal'],
            'tipe_transaksi' => ['nullable', 'string', '']
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        $siswaWallet = Auth::user()->siswa->first()->siswa_wallet;
        $startDate = request('tanggal_awal');
        $endDate = request('tanggal_akhir');
        $perPage = request('per_page', 10);
        $tipeTransaksi = request('tipe_transaksi');

        try {
            $startOfMonth = Carbon::create($endDate, $startDate, 1)->startOfMonth();
            $endOfMonth = Carbon::create($endDate, $startDate, 1)->endOfMonth();

            $query = $siswaWallet->siswa_wallet_riwayat()
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_riwayat', [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            }, function ($query) {
                $query->whereBetween('tanggal_riwayat', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ]);
            })
                ->latest();

            if ($tipeTransaksi) {
                $query->where('tipe_transaksi', $tipeTransaksi);
            }

            $siswaWalletRiwayat = $query->paginate($perPage);

            return response()->json(['data' => $siswaWalletRiwayat], Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('getRiwayat: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat mengambil data walet riwayat.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}