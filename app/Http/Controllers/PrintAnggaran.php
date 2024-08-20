<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class PrintInventaris extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $semua_anggaran = Anggaran::all();
        $filenya = Pdf::loadView('print.Anggaran', ['anggarans' => $semua_anggaran]);
        return $filenya->stream();
    }
}
