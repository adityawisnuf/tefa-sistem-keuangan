<?php

namespace App\Http\Controllers;

use App\Models\AsetSekolah;
use App\Models\PembayaranSiswa;
use App\Models\PembayaranPpdb;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Http\Request;


class RasioKeuanganController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil bulan dan tahun dari query string
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        // Mengambil data pemasukan dari pembayaran siswa dan PPDB
        $payments = PembayaranSiswa::where('status', 1)
            ->when($bulan, fn($query) => $query->whereMonth('created_at', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('created_at', $tahun))
            ->sum('nominal');
        $paymentsPpdb = PembayaranPpdb::where('status', 1)
            ->when($bulan, fn($query) => $query->whereMonth('created_at', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('created_at', $tahun))
            ->sum('nominal');
        $asset = AsetSekolah::when($bulan, fn($query) => $query->whereMonth('created_at', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('created_at', $tahun))
            ->sum('harga');
        $asetTetap = AsetSekolah::where('tipe', 'tetap') // Filter berdasarkan tipe aset
            ->when($bulan, fn($query) => $query->whereMonth('created_at', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('created_at', $tahun))
            ->sum('harga');
        
        // Mengambil data pengeluaran
        $expenses = Pengeluaran::when($bulan, fn($query) => $query->whereMonth('disetujui_pada', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('disetujui_pada', $tahun))
            ->sum('nominal');

        // Menghitung total pemasukan
        $totalPayment = $payments + $paymentsPpdb;

        // Menghitung laba bersih
        $profit = $totalPayment - $expenses;

        // Menghitung rasio 
        $npm = $this->netProfitMargin($profit, $totalPayment);
        $roa = $this->returnOnAsset($profit, $asset);
        $toa = $this->turnoverAsset($totalPayment, $asetTetap);
        $oer = $this->operatingExpenseRatio($expenses ,$totalPayment);

        // Menyiapkan data yang akan dikembalikan
        $data = [
            'net_profit_margin' => $npm,
            'return_on_asset' => $roa,
            'turnover_aset' => $toa,
            'operating_expense_ratio' => $oer,
        ];

        return response()->json(['data' => $data], 200);
    }

    //Rasio Profitabilitas
    private function netProfitMargin($profit, $totalPayment)
    {
        if ($totalPayment == 0) {
            return 'N/A'; // Menghindari pembagian dengan nol
        }
        return ($profit / $totalPayment)*100 ;
    }
    private function returnOnAsset($profit, $asset)
    {
        if ($asset == 0) {
            return 'N/A'; // Menghindari pembagian dengan nol
        }
        return ($profit / $asset)*100 ;
    }

    //Rasio efesiensi
    private function turnoverAsset($totalPayment, $asetTetap)
    {
        if ($asetTetap == 0) {
            return 'N/A'; // Menghindari pembagian dengan nol
        }
        return $totalPayment / $asetTetap;
    }

    private function operatingExpenseRatio($expenses, $totalPayment)
    {
        if ($totalPayment == 0) {
            return 'N/A'; // Menghindari pembagian dengan nol
        }
        return $expenses / $totalPayment ;
    }
}
