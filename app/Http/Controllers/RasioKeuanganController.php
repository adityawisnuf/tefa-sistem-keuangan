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
            ->when($bulan, fn($query) => $query->whereMonth('diajukan_pada', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('diajukan_pada', $tahun))
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
        $cr = $this->currentRatio($asetLancar, $currentLiability);
        $qr = $this->quickRatio($asetLancar, $inventory, $currentLiability);
        $npm = $this->netProfitMargin($profit, $totalPayment) * 100;
        $roa = $this->returnOnAsset($profit, $asset) * 100;
        $oer = $this->operatingExpenseRatio($expenses, $totalPayment) * 100;
        $toa = $this->turnoverAsset($totalPayment, $asetTetap);
        $dter = $this->debtToEquityRatio($totalLiability, $equity);
        $dr = $this->debtRatio($totalLiability, $asset)*100;

        $data = [
            'current_ratio' => $cr,
            'quick_ratio' => $qr,
            'net_profit_margin' => $npm,
            'return_on_assets' => $roa,
            'operating_expense_ratio' => $oer,
            'turnover_of_assets' => $toa,
            'debt_to_equity_ratio' => $dter,
            'debt_ratio' => $dr,
        ];
        $allZero = !array_filter($data, fn($value) => $value !== 0);

        // Return empty data array if all values are 0
        if ($allZero) {
            return response()->json(['data' => []], 200);
        }

        return response()->json(['data' => $data], 200);
    }

    private function currentRatio($asetLancar, $currentLiabilities)
    {
        return $currentLiabilities  == 0 ? 0 : $asetLancar / $currentLiabilities;
    }

    private function quickRatio($asetLancar, $inventory, $currentLiabilities)
    {
        return $currentLiabilities == 0 ? 0 : ($asetLancar - $inventory) / $currentLiabilities;
    }

    private function netProfitMargin($profit, $totalPayment)
    {
        return $totalPayment   == 0 ? 0 : $profit / $totalPayment;
    }

    private function returnOnAsset($profit, $asset)
    {
        return $asset  == 0 ? 0 : $profit / $asset;
    }

    private function operatingExpenseRatio($expenses, $totalPayment)
    {
        return $totalPayment  == 0 ? 0 : $expenses / $totalPayment;
    }

    private function turnoverAsset($totalPayment, $fixedAssets)
    {
        return $fixedAssets  == 0 ? 0 : $totalPayment / $fixedAssets;
    }

    private function debtToEquityRatio($totalLiabilities, $equity)
    {
        return $equity  == 0 ? 0 : $totalLiabilities / $equity;
    }
    private function debtRatio($totalLiabilities, $asset)
    {
        return $asset  == 0 ? 0 : $totalLiabilities / $asset;
    }
    public function getOptions()
    {

        // Gabungkan semua data
        $data = DB::table('pembayaran_siswa')
            ->selectRaw('YEAR(updated_at) as year, MONTHNAME(updated_at) as month')
            ->unionAll(
                DB::table('pembayaran_ppdb')->selectRaw('YEAR(created_at) as year, MONTHNAME(created_at) as month')
            )
            ->unionAll(
                DB::table('aset')->selectRaw('YEAR(created_at) as year, MONTHNAME(created_at) as month')
            )
            ->unionAll(
                DB::table('pengeluaran')->selectRaw('YEAR(diajukan_pada) as year, MONTHNAME(diajukan_pada) as month')
            )
            ->unionAll(
                DB::table('pengeluaran')->selectRaw('YEAR(disetujui_pada) as year, MONTHNAME(disetujui_pada) as month')
            )
            ->groupBy('year', 'month')
            ->get();

            $data = $data->filter(function ($item) {
                return !is_null($item->year) && !is_null($item->month);
            });

        $data = $data->filter(function ($item) {
            return !is_null($item->year) && !is_null($item->month);
        });
        $months = $data->pluck('month')->unique()->values()->toArray();
        $years = $data->pluck('year')->unique()->sortDesc()->values()->toArray();


        // Membuat mapping dari nama bulan ke angka bulan
        $monthNumbers = [
            'January' => '01',
            'February' => '02',
            'March' => '03',
            'April' => '04',
            'May' => '05',
            'June' => '06',
            'July' => '07',
            'August' => '08',
            'September' => '09',
            'October' => '10',
            'November' => '11',
            'December' => '12',
        ];

        // Format bulan dengan values dan labels
        $formattedMonths = [];
        foreach ($months as $month) {
            if (array_key_exists($month, $monthNumbers)) {
                $formattedMonths[] = [
                    'values' => $monthNumbers[$month],
                    'labels' => $month,
                ];
            } else {
                error_log("Month not found: $month");
            }
        }

        // Format tahun dengan values dan labels
        $formattedYears = [];
        foreach ($years as $year) {
            $formattedYears[] = [
                'values' => (string) $year,
                'labels' => (string) $year,
            ];
        }

        // Tambahkan opsi "semua" di awal list bulan dan tahun
    array_unshift($formattedMonths, [
        'values' => '',
        'labels' => 'Semua',
    ]);

    array_unshift($formattedYears, [
        'values' => '',
        'labels' => 'Semua',
    ]);


        return response()->json([
            'months' => $formattedMonths,
            'years' => $formattedYears,
        ]);
    }
}
