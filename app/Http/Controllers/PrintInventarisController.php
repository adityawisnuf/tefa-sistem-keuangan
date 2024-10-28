<?php

namespace App\Http\Controllers;

use App\Models\AsetSekolah;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class PrintInventaris extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $semua_asset = AsetSekolah::all();
        $filenya = Pdf::loadView('print.inventaris', ['assets' => $semua_asset]);
        return $filenya->stream();
    }
}
