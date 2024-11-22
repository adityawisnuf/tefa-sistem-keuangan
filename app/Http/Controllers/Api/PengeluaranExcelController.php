<?php

namespace App\Http\Controllers\Api;

use App\Exports\PengeluaranExport;
use App\Http\Controllers\Controller;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PengeluaranExcelController extends Controller
{
    public function exportPengeluaran(Request $request)
    {
        $query = Pengeluaran::with('pengeluaran_kategori', 'anggaran')->latest();

        // Filter berdasarkan tanggal diajukan
        if ($request->filled('diajukan')) {
            $query->whereDate('diajukan_pada', $request->diajukan);
        }

        // Filter berdasarkan tanggal disetujui
        if ($request->filled('disetujui')) {
            $query->whereDate('disetujui_pada', $request->disetujui);
        }

        $semua_pengeluaran = $query->get();
        return Excel::download(new PengeluaranExport($semua_pengeluaran), 'data_pengeluaran.xlsx');
    }
}