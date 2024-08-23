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

        // Format total transaksi menggunakan format mata uang Indonesia
        $totalNominal = PembayaranPpdb::sum('nominal');
        $totalSemuaPembayaran = number_format($totalNominal, 0, ',', '.');

           // Jika tidak ada data yang ditemukan
           if ($laporan->isEmpty()) {
            return response()->json([
                'laporan_keuangan' => [],
                'total_semua_pembayaran' => '0'
            ], 200);
        }

        // Format total_nominal pada laporan
        $laporan->getCollection()->transform(function ($item) {
            $item->total_nominal = number_format($item->total_nominal, 0, ',', '.');
            return $item;
        });

        // Mengembalikan data dalam format JSON
        return response()->json([
            'laporan_keuangan' => $laporan,
            'total_semua_pembayaran' => $totalSemuaPembayaran
        ], 200);
    }

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
    
        // Apply filters based on query parameters
        if ($request->has('nama')) {
            $query->whereHas('pendaftar', function ($q) use ($request) {
                $q->where('nama_depan', 'like', '%' . $request->input('nama') . '%')
                  ->orWhere('nama_belakang', 'like', '%' . $request->input('nama') . '%');
            });
        }
    
        if ($request->has('status')) {
            $query->where('pembayaran_ppdb.status', $request->input('status'));
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
    
        // Format total_nominal in each item of the report
        $laporan->getCollection()->transform(function ($item) {
            $item->total_nominal = number_format($item->total_nominal, 0, ',', '.');
            return $item;
        });
    
        // Format total_nominal for total payments
        $totalNominal = PembayaranPpdb::sum('nominal');
        $totalSemuaPembayaran = number_format($totalNominal, 0, ',', '.');
    
        // Return data in JSON format
        return response()->json([
            'laporan_keuangan' => $laporan,
            'total_semua_pembayaran' => $totalSemuaPembayaran
        ]);
    }
    

}
