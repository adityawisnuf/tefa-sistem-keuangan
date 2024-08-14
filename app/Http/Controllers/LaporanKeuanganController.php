<?php

namespace App\Http\Controllers;

use App\Models\PembayaranPpdb;
use Illuminate\Http\Request;

class LaporanKeuanganController extends Controller
{
    /**
     * Menampilkan laporan keuangan PPDB.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function laporanKeuangan(Request $request)
    {
        $laporan = PembayaranPpdb::selectRaw('
                pembayaran_ppdb.ppdb_id,
                pembayaran_ppdb.status,
                SUM(pembayaran_ppdb.nominal) as total_nominal,
                pendaftar.nama_depan,
                pendaftar.nama_belakang
            ')
            ->leftJoin('pendaftar', 'pembayaran_ppdb.ppdb_id', '=', 'pendaftar.ppdb_id')
            ->groupBy('pembayaran_ppdb.ppdb_id', 'pembayaran_ppdb.status', 'pendaftar.nama_depan', 'pendaftar.nama_belakang')
            ->paginate(5);
    
        $totalSemuaPembayaran = PembayaranPpdb::sum('nominal');
    
        // Mengembalikan data dalam format JSON
        return response()->json([
            'laporan_keuangan' => $laporan,
            'total_semua_pembayaran' => $totalSemuaPembayaran
        ], 200);
    }
    
}
