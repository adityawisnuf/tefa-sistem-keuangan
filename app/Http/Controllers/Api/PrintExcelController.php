<?php

namespace App\Http\Controllers\Api;

use App\Models\PembayaranSiswa;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PembayaranExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrintExcelController extends Controller
{
    public function exportExcel(Request $request)
    {
        $query = PembayaranSiswa::with('pembayaran_kategori', 'pembayaran', 'siswa.kelas')->latest();

        // Filter berdasarkan berbagai parameter
        if ($request->filled('kode_transaksi') && $request->input('kode_transaksi') !== null && $request->input('kode_transaksi') !== "null") {
            $query->where('merchant_order_id', 'like', '%' . $request->kode_transaksi . '%');
        }

        if ($request->filled('nama_siswa') && $request->input('nama_siswa') !== null && $request->input('nama_siswa') !== "null") {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('id', $request->nama_siswa);
            });
        }

        if ($request->filled('kelas') && $request->input('kelas') !== null && $request->input('kelas') !== "null") {
            $query->whereHas('siswa.kelas', function ($q) use ($request) {
                $q->where('id', $request->kelas);
            });
        }

        if ($request->filled('jenis_pembayaran') && $request->input('jenis_pembayaran') !== null && $request->input('jenis_pembayaran') !== "null") {
            $query->whereHas('pembayaran.pembayaran_kategori', function ($q) use ($request) {
                $q->where('jenis_pembayaran', $request->jenis_pembayaran);
            });
        }

        // Logika rekapitulasi berdasarkan input pengguna
if ($request->rekapitulasi !== 'Semua') {
    if ($request->rekapitulasi === 'Harian') {
        $query->whereDate('created_at', $request->filled('tanggal_pembayaran') ? $request->tanggal_pembayaran : now());
    } elseif ($request->rekapitulasi === 'Bulanan') {
        // Mengambil bulan dan tahun dari bulan_pembayaran
        if ($request->filled('bulan_pembayaran')) {
            // Menggunakan explode untuk mendapatkan bulan dan tahun
            list($tahun, $bulan) = explode('-', $request->bulan_pembayaran);
            $query->whereYear('created_at', $tahun)
                  ->whereMonth('created_at', $bulan);
        } else {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        }
    } elseif ($request->rekapitulasi === 'Tahunan') {
        $query->whereYear('created_at', $request->filled('tahun_pembayaran') ? $request->tahun_pembayaran : now()->year);
    }
}


        $allPayment = $query->get();
        return Excel::download(new PembayaranExport($allPayment), 'data_pembayaran.xlsx');
    }
}
