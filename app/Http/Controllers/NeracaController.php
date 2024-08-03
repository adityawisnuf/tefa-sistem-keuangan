<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\Request;

class NeracaController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Ambil data transaksi berdasarkan periode
        $transaksi = Transaksi::whereBetween('tanggal', [$startDate, $endDate])->get();

        // Data dummy transaksi (jika belum ada data di database)
        if ($transaksi->isEmpty()) {
            $transaksi = collect([
                ['jenis' => 'pendapatan', 'jumlah' => 100000],
                ['jenis' => 'pengeluaran', 'jumlah' => 50000],
                ['jenis' => 'pendapatan', 'jumlah' => 80000],
                ['jenis' => 'pengeluaran', 'jumlah' => 30000],
            ]);
        }

        // Inisialisasi variabel untuk menampung total
        $totalPendapatan = 0;
        $totalPengeluaran = 0;

        // Hitung total pendapatan dan pengeluaran
        foreach ($transaksi as $t) {
            if ($t->jenis === 'pendapatan') {
                $totalPendapatan += $t->jumlah;
            } else {
                $totalPengeluaran += $t->jumlah;
            }
        }

        // Hitung neraca (aset)
        $neraca = $totalPendapatan - $totalPengeluaran;

        // Kembalikan respons dalam bentuk JSON
        return response()->json([
            'total_pendapatan' => $totalPendapatan,
            'total_pengeluaran' => $totalPengeluaran,
            'neraca' => $neraca,
        ]);
    }

    public function store(Request $request)
    {
        $transaksi = Transaksi::create($request->all());
        return response()->json($transaksi, 201);
    }
}
