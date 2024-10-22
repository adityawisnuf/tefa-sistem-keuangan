<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use Barryvdh\DomPDF\Facade\Pdf; 

class PrintPdfSPPController extends Controller
{
    public function __invoke(Request $request)
    {
        $semua_pembayaran_spp = Siswa::with('kelas', 'orangtua')->get(); 
        $pdf = Pdf::loadView('print.PrintPdfSPP', ['pembayaranSiswas' => $semua_pembayaran_spp]);
        return $pdf->stream('Data_Pembayaran_SPP.pdf'); 
    }
}
