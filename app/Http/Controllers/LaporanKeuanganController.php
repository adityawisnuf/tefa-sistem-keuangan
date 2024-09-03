<?php

namespace App\Http\Controllers;

use App\Models\PembayaranPpdb;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanKeuanganController extends Controller
{
    /**
     * Menampilkan laporan keuangan PPDB.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchLaporanKeuangan(Request $request)
{
    $query = PembayaranPpdb::with([
        'pendaftar',
        'pembayaran'
    ])->select(
        'pembayaran_ppdb.ppdb_id',
        'pembayaran_ppdb.status',
        'pembayaran_ppdb.pembayaran_id',
        DB::raw('SUM(pembayaran_ppdb.nominal) as total_nominal')
    )
    ->leftJoin('pendaftar', 'pembayaran_ppdb.ppdb_id', '=', 'pendaftar.ppdb_id')
    ->leftJoin('pembayaran', 'pembayaran_ppdb.pembayaran_id', '=', 'pembayaran.id')
    ->groupBy('pembayaran_ppdb.ppdb_id', 'pembayaran_ppdb.status', 'pendaftar.nama_depan', 'pendaftar.nama_belakang', 'pembayaran_ppdb.pembayaran_id');

    
    if ($request->has('nama')) {
        $query->whereHas('pendaftar', function ($q) use ($request) {
            $q->where('nama_depan', 'like', '%' . $request->input('nama') . '%')
              ->orWhere('nama_belakang', 'like', '%' . $request->input('nama') . '%');
        });
    }

    if ($request->has('status')) {
        $query->where('pembayaran_ppdb.status', $request->input('status'));
    }

    if ($request->has('tahun_awal')) {
        $query->whereYear('pembayaran.created_at', '=', $request->input('tahun_awal'));
    }

    if ($request->has('tahun_akhir')) {
        $query->whereYear('pembayaran.created_at', '<=', $request->input('tahun_akhir'));
    }

    if ($request->has('tahun_ajaran')) {
        $query->whereRaw('
            CONCAT(
                IF(MONTH(pembayaran.created_at) >= 7, YEAR(pembayaran.created_at), YEAR(pembayaran.created_at) - 1),
                "/",
                IF(MONTH(pembayaran.created_at) >= 7, YEAR(pembayaran.created_at) + 1, YEAR(pembayaran.created_at))
            ) like ?
        ', ['%' . $request->input('tahun_ajaran') . '%']);
    }

    // Paginate the results
    $laporan = $query->paginate(5);

    $laporan->getCollection()->transform(function ($item) {
        $item->total_nominal = number_format($item->total_nominal, 0, ',', '.');
        return $item;
    });

    $totalNominal = PembayaranPpdb::where('status', 1)->sum('nominal');
    $totalSemuaPembayaran = number_format($totalNominal, 0, ',', '.');

    return response()->json([
        'laporan_keuangan' => $laporan,
        'total_semua_pembayaran' => $totalSemuaPembayaran
    ]);
}



}
