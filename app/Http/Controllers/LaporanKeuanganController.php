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
        // Validasi parameter
        $request->validate([
            'tahun_awal' => 'nullable|integer|digits:4',
            'tahun_akhir' => 'nullable|integer|digits:4|gte:tahun_awal',
            'status' => 'nullable|integer|in:0,1,', // Sesuaikan dengan nilai status yang ada
        ]);

        // Ambil parameter dari request
        $tahunAwal = $request->input('tahun_awal');
        $tahunAkhir = $request->input('tahun_akhir');
        $status = $request->input('status');

        // Mulai query
        $query = PembayaranPpdb::selectRaw('
                pembayaran_ppdb.ppdb_id,
                pembayaran_ppdb.status,
                SUM(pembayaran_ppdb.nominal) as total_nominal,
                pendaftar.nama_depan,
                pendaftar.nama_belakang,
                CONCAT(
                    IF(MONTH(pembayaran.created_at) >= 7, YEAR(pembayaran.created_at), YEAR(pembayaran.created_at) - 1),
                    "/",
                    IF(MONTH(pembayaran.created_at) >= 7, YEAR(pembayaran.created_at) + 1, YEAR(pembayaran.created_at))
                ) as tahun_ajaran
            ')
            ->leftJoin('pendaftar', 'pembayaran_ppdb.ppdb_id', '=', 'pendaftar.ppdb_id')
            ->leftJoin('pembayaran', 'pembayaran_ppdb.pembayaran_id', '=', 'pembayaran.id'); // Tambahkan join dengan tabel pembayaran

       // Filter berdasarkan tahun jika ada
        if ($tahunAwal) {
            $query->whereYear('pembayaran.created_at', '>=', $tahunAwal);
        }

        if ($tahunAkhir) {
            $query->whereYear('pembayaran.created_at', '<=', $tahunAkhir);
        }

        if (isset($status)) {
            $query->where('pembayaran_ppdb.status', $status);
        }

        $laporan = $query->groupBy('pembayaran_ppdb.ppdb_id', 'pembayaran_ppdb.status', 'pendaftar.nama_depan', 'pendaftar.nama_belakang', 'tahun_ajaran')
                          ->paginate(5);

        $totalSemuaPembayaran = PembayaranPpdb::sum('nominal');

           // Jika tidak ada data yang ditemukan
           if ($laporan->isEmpty()) {
            return response()->json([
                'laporan_keuangan' => [],
                'total_semua_pembayaran' => 0
            ], 200);
        }

        // Mengembalikan data dalam format JSON
        return response()->json([
            'laporan_keuangan' => $laporan,
            'total_semua_pembayaran' => $totalSemuaPembayaran
        ], 200);

    }
}
