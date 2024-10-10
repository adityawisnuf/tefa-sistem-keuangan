<?php

namespace App\Http\Controllers\Api;

use App\Exports\PengeluaranExport;
use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PengeluaranExcelController extends Controller
{
    public function exportPengeluaran()
    {
        // Mengambil semua data pengeluaran beserta relasi kategori dan anggaran
        $semua_pengeluaran = Pengeluaran::with('pengeluaran_kategori', 'anggaran')->get();

        // Menggunakan Excel facade untuk mengunduh file dalam format .xlsx
        return Excel::download(new PengeluaranExport($semua_pengeluaran), 'data_pengeluaran.xlsx');
    }
}
