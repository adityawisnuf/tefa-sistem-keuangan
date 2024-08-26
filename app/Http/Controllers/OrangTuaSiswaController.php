<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Symfony\Component\HttpFoundation\Response;

class OrangTuaSiswaController extends Controller
{
    public function getDataSiswa()
    {
        $orangtua = Auth::user()->orangtua->firstOrFail();

        $perPage = request('per_page', 10);
        $nama_siswa = request('nama_siswa', null);
        $month = request('month', Carbon::now()->month);
        $year = request('year', Carbon::now()->year);

        $startOfMonth = Carbon::create($year, $month, 1);
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $siswa = $orangtua->siswa()
            ->with([
                'siswa_wallet',
                'siswa_wallet.siswa_wallet_riwayat' => function ($query) use ($startOfMonth, $endOfMonth) {
                    $query
                        ->select(
                            'siswa_wallet_id',
                            DB::raw('SUM(CASE WHEN tipe_transaksi = "pemasukan" THEN nominal ELSE 0 END) as total_pemasukan'),
                            DB::raw('SUM(CASE WHEN tipe_transaksi = "pengeluaran" THEN nominal ELSE 0 END) as total_pengeluaran'),
                        )
                        ->whereBetween('tanggal_riwayat', [$startOfMonth, $endOfMonth])
                        ->groupBy('siswa_wallet_id');
                }
            ])
            ->paginate($perPage);

        $siswa->getCollection()->transform(function ($siswa) {
            return [
                'siswa_id' => $siswa->id,
                'nama_siswa' => $siswa->nama_depan . ' ' . $siswa->nama_belakang,
                'saldo_siswa' => $siswa->siswa_wallet->nominal ?? 0,
                'total_pemasukan' => $siswa->siswa_wallet->siswa_wallet_riwayat->first()->total_pemasukan ?? 0,
                'total_pengeluaran' => $siswa->siswa_wallet->siswa_wallet_riwayat->first()->total_pengeluaran ?? 0,
            ];
        });

        if ($nama_siswa) {
            $siswa = $siswa->filter(function ($item) use ($nama_siswa) {
                return stripos($item['nama_siswa'], $nama_siswa) !== false; 
            });
        }

        return response()->json(['data' => $siswa], Response::HTTP_OK);
    }
}
