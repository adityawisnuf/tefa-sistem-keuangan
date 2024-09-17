<?php

namespace App\Http\Controllers;

use App\Models\Pengeluaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfPengeluaran extends Controller
{
    public function __invoke(Request $request)
    {
        $pengeluarans = Pengeluaran::with('pengeluaran_kategori', 'anggaran')->get();
        $fileName = 'Laporan Pengeluaran Buku Kas.pdf';
        $pdf = Pdf::loadView('print.pengeluaran', ['pengeluarans' => $pengeluarans]);
        return $pdf->stream($fileName);
    }
}

