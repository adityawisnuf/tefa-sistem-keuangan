<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswa;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintBukuKasController extends Controller
{
    public function __invoke(Request $request)
    {
        $pembayarans = PembayaranSiswa::with('pembayaran_kategori', 'pembayaran', 'siswa')->get();
        $fileName = 'Laporan Buku Kas.pdf';
        $pdf = Pdf::loadView('print.pembayaran', ['pembayarans' => $pembayarans]);
        return $pdf->stream($fileName);
    }
}
