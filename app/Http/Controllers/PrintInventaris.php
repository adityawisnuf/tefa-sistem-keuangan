<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class PrintInventaris extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $semua_asset = Asset::all();
        $filenya = Pdf::loadView('print.inventaris', ['assets' => $semua_asset]);
        return $filenya->stream();
    }
}
