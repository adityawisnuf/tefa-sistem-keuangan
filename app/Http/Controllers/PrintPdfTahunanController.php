<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf; 

class PrintPdfTahunanController extends Controller
{
    public function __invoke(Request $request)
    {
        $semua_pembayaran_tahunan = Siswa::with('kelas', 'orangtua')->get(); 
        $pdf = Pdf::loadView('print.PrintPdfTahunan', ['pembayaranSiswas' => $semua_pembayaran_tahunan]);
        return $pdf->stream('Data_Pembayaran_Tahunan.pdf'); 
    }
}
