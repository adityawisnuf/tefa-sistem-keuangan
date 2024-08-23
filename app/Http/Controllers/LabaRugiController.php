<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\AsetSekolah;
use App\Models\PembayaranPpdb;
use App\Models\PembayaranSiswa;
use App\Models\Pengeluaran;
use App\Models\PengeluaranKategori;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class LabaRugiController extends Controller
{
    protected $startDate;
    protected $endDate;

    public function index(Request $request)
    {
        try {
            $bulan = $request->query('bulan');
            $tahun = $request->query('tahun');

            // Set tanggal mulai dan akhir
            $this->startDate = Carbon::createFromDate($tahun ?? date('Y'), $bulan ?? date('m'), 1);
            $this->endDate = $this->startDate->copy()->endOfMonth();

            if ($tahun && !$bulan) {
                $this->startDate = Carbon::createFromDate($tahun, 1, 1);
                $this->endDate = Carbon::createFromDate($tahun, 12, 31);
            }

            $financialData = $this->retrieveFinancialData();
            $financialMetrics = $this->calculateFinancialMetrics($financialData);

            // Ambil kategori pengeluaran
            $categories = PengeluaranKategori::pluck('nama', 'id')->toArray();

            // Kelompokkan pengeluaran berdasarkan kategori
            $pengeluaranByCategory = $financialData['expenditures']->groupBy('pengeluaran_kategori_id')->map(function ($items) use ($categories) {
                $kategoriId = $items->first()->pengeluaran_kategori_id;
                return [
                    'keperluan' => $categories[$kategoriId] ?? 'Tidak Diketahui',
                    'nominal' => $items->sum('nominal')
                ];
            })->values();

            // Hitung rasio laba bersih dan beban operasional
            $rasioLabaBersih = $this->formatPercentage($financialMetrics['profit'], $financialMetrics['totalPayment']);
            $rasioBeban = $this->formatPercentage($financialMetrics['totalExpenditure'], $financialMetrics['totalPayment']);

            // Jika tidak ada data, kembalikan data kosong
            if (!$financialMetrics['totalPayment'] && $pengeluaranByCategory->isEmpty()) {
                return response()->json(['data' => []], 200);
            }

            $data = [
                'pendapatan' => $financialMetrics['totalPayment'],
                'pengeluaran' => $pengeluaranByCategory,
                'pengeluaran_total' => $financialMetrics['totalExpenditure'],
                'laba_bersih' => $financialMetrics['profit'],
                'rasio_laba_bersih' => $rasioLabaBersih,
                'rasio_beban_operasional' => $rasioBeban,
            ];

            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            // Log error
            logger()->error($e->getMessage());
            // Return error response
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }

    private function retrieveFinancialData()
    {
        // Mengambil data Pembayaran dan Pengeluaran
        $payments = PembayaranSiswa::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 1);
        $paymentsPpdb = PembayaranPpdb::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 1);
        $expenditures = Pengeluaran::whereBetween('disetujui_pada', [$this->startDate, $this->endDate])->get();

        return [
            'payments' => $payments,
            'paymentsPpdb' => $paymentsPpdb,
            'expenditures' => $expenditures,
        ];
    }

    private function calculateFinancialMetrics(array $financialData)
    {
        $totalPayment = $financialData['payments']->sum('nominal') + $financialData['paymentsPpdb']->sum('nominal');
        $totalExpenditure = $financialData['expenditures']->sum('nominal');
        $profit = $totalPayment - $totalExpenditure;

        if ($profit < 0) {
            $profit = 0;
        }

        return [
            'totalPayment' => $totalPayment,
            'totalExpenditure' => $totalExpenditure,
            'profit' => $profit,
        ];
    }

    private function formatPercentage($value, $divisor)
    {
        if ($divisor) return ($value / $divisor) * 100;
        return $value > 0 ? 100.0 : 0.0;
    }

    public function getOptions()
{

    // Gabungkan semua data
    $data = DB::table('pembayaran_siswa')
    ->selectRaw('YEAR(created_at) as year, MONTHNAME(created_at) as month')
    ->unionAll(
        DB::table('anggaran')->selectRaw('YEAR(created_at) as year, MONTHNAME(created_at) as month')
    )
    ->unionAll(
        DB::table('aset')->selectRaw('YEAR(created_at) as year, MONTHNAME(created_at) as month')
    )
    ->unionAll(
        DB::table('pembayaran_ppdb')->selectRaw('YEAR(created_at) as year, MONTHNAME(created_at) as month')
    )
    ->unionAll(
        DB::table('pengeluaran')->selectRaw('YEAR(created_at) as year, MONTHNAME(created_at) as month')
    )
    ->groupBy('year', 'month')
    ->get();

// Extract unique months and years
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
        // Check if the month exists in the mapping
        if (array_key_exists($month, $monthNumbers)) {
            $formattedMonths[] = [
                'values' => $monthNumbers[$month],
                'labels' => $month,
            ];
        } else {
            // Handle the case where the month is not found
            // You can log an error, return a default value, or ignore it
            // For example:
            error_log("Month not found: $month");
        }
    }

    return response()->json([
        'months' => $formattedMonths,
        'years' => $years,
    ]);
}

}
