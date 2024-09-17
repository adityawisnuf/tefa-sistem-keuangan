<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswa;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PembayaranExport;

class PrintExcelController extends Controller
{
    public function exportExcel()
    {
        $semua_pembayaran = PembayaranSiswa::with('siswa', 'pembayaran_kategori', 'pembayaran')->get();

        return Excel::download(new PembayaranExport($semua_pembayaran), 'data_pembayaran.xlsx');
    }
}
