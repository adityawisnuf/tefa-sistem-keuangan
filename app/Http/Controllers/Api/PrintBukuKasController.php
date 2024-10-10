<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PembayaranSiswa;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintBukuKasController extends Controller
{
    public function __invoke(Request $request)
    {
        // Logika filter berdasarkan rekapitulasi dan input request
        $query = PembayaranSiswa::with('pembayaran_kategori', 'pembayaran', 'siswa');
    
        // Filter berdasarkan rekapitulasi
        if ($request->rekapitulasi === 'Harian') {
            $query->whereDate('created_at', $request->filled('tanggal_pembayaran') ? $request->tanggal_pembayaran : now());
        } elseif ($request->rekapitulasi === 'Bulanan') {
            $query->whereMonth('created_at', $request->filled('bulan_pembayaran') ? $request->bulan_pembayaran : now()->month)
                  ->whereYear('created_at', $request->filled('tahun_pembayaran') ? $request->tahun_pembayaran : now()->year);
        } elseif ($request->rekapitulasi === 'Tahunan') {
            $query->whereYear('created_at', $request->filled('tahun_pembayaran') ? $request->tahun_pembayaran : now()->year);
        }
    
        // Filter tambahan lainnya
        if ($request->filled('kode_transaksi')) {
            $query->where('merchant_order_id', 'like', '%' . $request->kode_transaksi . '%');
        }
    
        if ($request->filled('nama_siswa')) {
            $query->whereHas('siswa', function($q) use ($request) {
                $q->where('id', $request->nama_siswa);
            });
        }
    
        if ($request->filled('kelas')) {
            $query->whereHas('siswa.kelas', function($q) use ($request) {
                $q->where('id', $request->kelas);
            });
        }
    
        if ($request->filled('jenis_pembayaran')) {
            $query->whereHas('pembayaran.pembayaran_kategori', function($q) use ($request) {
                $q->where('jenis_pembayaran', $request->jenis_pembayaran);
            });
        }
    
        // Mengambil data yang sesuai filter
        $pembayarans = $query->get();
    
        // Cek jika data kosong
        if ($pembayarans->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data pembayaran yang sesuai dengan filter.'
            ], 404);
        }
    
        // Proses export PDF
        $fileName = 'Laporan Buku Kas.pdf';
        $pdf = Pdf::loadView('print.pembayaran', ['pembayarans' => $pembayarans]);
        return $pdf->stream($fileName);
    }    
}
