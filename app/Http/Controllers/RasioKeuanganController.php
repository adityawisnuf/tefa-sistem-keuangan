<?php

namespace App\Http\Controllers;

use App\Models\AsetSekolah;
use App\Models\PembayaranSiswa;
use App\Models\PembayaranPpdb;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
        $asetLancar = AsetSekolah::where('tipe', 'lancar') // Filter berdasarkan tipe aset
            ->when($bulan, fn($query) => $query->whereMonth('created_at', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('created_at', $tahun))
            ->sum('harga');

        // Mengambil data pengeluaran
        $expenses = Pengeluaran::whereNotNull('disetujui_pada')
            ->when($bulan, fn($query) => $query->whereMonth('disetujui_pada', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('disetujui_pada', $tahun))
            ->sum('nominal');
        $totalLiability = Pengeluaran::whereNull('disetujui_pada')
            ->when($bulan, fn($query) => $query->whereMonth('disetujui_pada', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('disetujui_pada', $tahun))
            ->sum('nominal');

        $currentLiability = DB::table('pengeluaran')
            ->join('pengeluaran_kategori', 'pengeluaran.pengeluaran_kategori_id', '=', 'pengeluaran_kategori.id')
            ->whereNull('pengeluaran.disetujui_pada')
            ->where('pengeluaran_kategori.tipe_utang', 'jangka pendek')
            ->when($bulan, fn($query) => $query->whereMonth('pengeluaran.created_at', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('pengeluaran.created_at', $tahun))
            ->sum('pengeluaran.nominal');


        $inventory = DB::table('pengeluaran')
            ->join('pengeluaran_kategori', 'pengeluaran.pengeluaran_kategori_id', '=', 'pengeluaran_kategori.id')
            ->where('pengeluaran_kategori.nama', 'Barang Habis Pakai')
            ->when($bulan, fn($query) => $query->whereMonth('pengeluaran.created_at', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('pengeluaran.created_at', $tahun))
            ->sum('pengeluaran.nominal');

        $equity = $asset - $totalLiability;




        // Menghitung total pemasukan
        $totalPayment = $payments + $paymentsPpdb;

        // Menghitung laba bersih
        $profit = $totalPayment - $expenses;

        // Menghitung rasio
        $cr = $this->currentRatio($asetLancar, $currentLiability) ;
        $qr = $this->quickRatio($asetLancar, $inventory, $currentLiability);
        $npm = $this->netProfitMargin($profit, $totalPayment)*100;
        $roa = $this->returnOnAsset($profit, $asset)*100;
        $oer = $this->operatingExpenseRatio($expenses ,$totalPayment)*100;
        $toa = $this->turnoverAsset($totalPayment, $asetTetap);
        $dter = $this->debtToEquityRatio($asset,$equity);
        $dr = $this->debtRatio($totalLiability,$asset)*100;


        // Menyiapkan data yang akan dikembalikan
        $data = [
            'current_ratio' => $cr,
            'quick_ratio' => $qr,
            'net_profit_margin' => $npm,
            'return_on_asset' => $roa,
            'operating_expense_ratio' => $oer,
            'turnover_aset' => $toa,
            'debt_to_equity_ratio' => $dter,
            'debt_ratio' => $dr,
        ];

        return response()->json(['data' => $data], 200);
    }

    //Rasio Profitabilitas
    private function netProfitMargin($profit, $totalPayment)
    {
        if ($totalPayment == 0) {
            return 'N/A'; // Menghindari pembagian dengan nol
        }
        return ($profit / $totalPayment) ;
    }
    private function returnOnAsset($profit, $asset)
    {
        if ($asset == 0) {
            return 'N/A'; // Menghindari pembagian dengan nol
        }
        return ($profit / $asset);
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

    //Rasio likuiditas
    private function currentRatio($asetLancar, $currentLiability){
        if ($currentLiability == 0) {
            return 'N/A'; // Menghindari pembagian dengan nol
        }
        return $asetLancar / $currentLiability;
    }
    private function quickRatio($asetLancar,$inventory, $currentLiability){
        if ($currentLiability == 0) {
            return 'N/A'; // Menghindari pembagian dengan nol
        }
        return ($asetLancar - $inventory) / $currentLiability;
    }
    //Rasio solvabilitas
    private function debtToEquityRatio($totalLiability, $equity){
        if ($equity == 0) {
            return 'N/A'; // Menghindari pembagian dengan nol
        }
        return $totalLiability / $equity;
    }
    private function debtRatio($totalLiability, $asset){
        if ($asset == 0) {
            return 'N/A'; // Menghindari pembagian dengan nol
        }
        return $totalLiability / $asset;
    }
}
