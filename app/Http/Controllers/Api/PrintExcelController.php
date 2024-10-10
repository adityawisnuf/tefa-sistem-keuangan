<?php

namespace App\Http\Controllers\Api;

use App\Models\PembayaranSiswa;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PembayaranExport;
use App\Http\Controllers\Controller;

class PrintExcelController extends Controller
{
    public function exportExcel()
    {
        // Mengambil semua data pembayaran dengan relasi yang diperlukan
        $semua_pembayaran = PembayaranSiswa::with('siswa', 'pembayaran_kategori', 'pembayaran')->get();

        // Mengunduh file Excel dengan nama 'data_pembayaran.xlsx'
        return Excel::download(new PembayaranExport($semua_pembayaran), 'data_pembayaran.xlsx');
    }
}
