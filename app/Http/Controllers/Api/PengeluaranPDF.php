<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfPengeluaran extends Controller
{
    public function __invoke(Request $request)
    {
        // Ambil parameter tanggal dari request
        $tgl_awal = $request->input('tgl_awal');
        $tgl_akhir = $request->input('tgl_akhir');
        $diajukan = $request->input('diajukan'); // Tanggal diajukan
        $disetujui = $request->input('disetujui'); // Tanggal disetujui

        // Query untuk mengambil data pengeluaran
        $query = Pengeluaran::with('pengeluaran_kategori', 'anggaran');

        // Filter berdasarkan tanggal diajukan jika ada
        if ($diajukan) {
            $query->whereDate('diajukan_pada', $diajukan);
        }

        // Filter berdasarkan tanggal disetujui jika ada
        if ($disetujui) {
            $query->whereDate('disetujui_pada', $disetujui);
        }

        // Jika tanggal awal dan akhir diisi, gunakan whereBetween
        if ($tgl_awal && $tgl_akhir) {
            $query->whereBetween('created_at', [$tgl_awal, $tgl_akhir]);
        }

        // Ambil data pengeluaran sesuai filter
        $pengeluarans = $query->get();

        // Nama file PDF
        $fileName = 'Laporan Pengeluaran Buku Kas.pdf';

        // Siapkan data untuk view
        $pdf = Pdf::loadView('print.pengeluaran', ['pengeluarans' => $pengeluarans]);

        return $pdf->stream($fileName);
    }
}
