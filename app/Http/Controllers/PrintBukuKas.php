<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswa;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PrintBukuKas extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $semua_pembayaran = PembayaranSiswa::all();
        $filenya = Pdf::loadView('print.pembayaran', ['pembayarans' => $semua_pembayaran]);
        return $filenya->stream();
    }
}
