<?php

namespace App\Http\Controllers;

use App\Exports\PengeluaranExport;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PengeluaranExcelController extends Controller
{
    public function exportPengeluaran()
    {
        $semua_pengeluaran = Pengeluaran::with('pengeluaran_kategori', 'anggaran')->get();
        return Excel::download(new PengeluaranExport($semua_pengeluaran), 'data_pengeluaran.xlsx');
    }
}
