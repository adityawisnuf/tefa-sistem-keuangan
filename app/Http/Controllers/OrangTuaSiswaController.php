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
        $orangtua = Auth::user()->orangtua()->firstOrFail();

        $month = request()->input('month', Carbon::now()->month);
        $year = request()->input('year', Carbon::now()->year);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $siswa = $orangtua->siswa()
            ->with('siswa_wallet')
            ->with([
                'siswa_wallet.siswa_wallet_riwayat' => function ($query) use ($startOfMonth, $endOfMonth) {
                    $query->select(
                        'siswa_wallet_id',
                        DB::raw('SUM(CASE WHEN tipe_transaksi = "pemasukan" THEN nominal ELSE 0 END) as total_pemasukan'),
                        DB::raw('SUM(CASE WHEN tipe_transaksi = "pengeluaran" THEN nominal ELSE 0 END) as total_pengeluaran')
                    )
                        ->whereBetween('tanggal_riwayat', [$startOfMonth, $endOfMonth])
                        ->groupBy('siswa_wallet_id');
                }
            ])
            ->get();

        $siswa->transform(function ($siswa) {
            $totalPemasukan = $siswa->siswa_wallet->siswa_wallet_riwayat->first()->total_pemasukan ?? 0;
            $totalPengeluaran = $siswa->siswa_wallet->siswa_wallet_riwayat->first()->total_pengeluaran ?? 0;

            return [
                'siswa_id' => $siswa->id,
                'nama_siswa' => $siswa->nama_depan . ' ' . $siswa->nama_belakang,
                'saldo_sekarang' => 'Rp' . Number::format($siswa->siswa_wallet->nominal ?? 0, 0, 0, 'id-ID'),
                'total_pemasukan' => 'Rp' . Number::format($totalPemasukan, 0, 0, 'id-ID'),
                'total_pengeluaran' => 'Rp' . Number::format($totalPengeluaran, 0, 0, 'id-ID'),
            ];
        });

        return response()->json(['data' => $siswa], Response::HTTP_OK);
    }

    public function getTransaksiKantinSiswa()
    {
        $orangTua = Auth::user()->orangtua()->with('siswa')->firstOrFail();
        $siswaId = request('siswa_id', null);
        $perPage = request('per_page', 10);

        //nama produk
        //harga produk
        //qty
        //harga total
        //tanggal
        //status

        $siswa = $orangTua->siswa()->findOrFail($siswaId);

        $riwayat = $siswa->kantin_transaksi()
            ->whereIn('status', ['dibatalkan', 'selesai'])
            ->with('kantin_transaksi_detail.kantin_produk')
            ->paginate($perPage);

            dd('test');

        $formattedData = $riwayat->map(function ($transaksi) {
            return [
                'item_detail' => $transaksi->kantin_transaksi_detail->map(function ($detail) {
                    return [
                        'nama_produk' => $detail->kantin_produk->nama_produk,
                        'harga_jual' => $detail->kantin_produk->harga_jual,
                        'jumlah' => $detail->jumlah,
                        'harga_total' => $detail->kantin_produk->harga_jual * $detail->jumlah,
                    ];
                }),
                'status' => $transaksi->status,
            ];
        });

        return response()->json(['data' => $formattedData], Response::HTTP_OK);

    }
}
