<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswa;
use App\Models\Pengeluaran;
use Carbon\Carbon;
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

            $this->startDate = Carbon::createFromDate($tahun ?? date('Y'), $bulan ?? 1, 1);
            $this->endDate = $bulan ? $this->startDate->copy()->endOfMonth() : Carbon::createFromDate($tahun, 12, 31);

            // if ($bulan && $tahun && $bulan != '') {
            //     $this->startDate = Carbon::createFromDate($tahun, $bulan, 1);
            //     $this->endDate = $this->startDate->copy()->endOfMonth();
            // } elseif ($tahun) {
            //     $this->startDate = Carbon::createFromDate($tahun, 1, 1);
            //     $this->endDate = Carbon::createFromDate($tahun, 12, 31);
            // }

            $financialData = $this->retrieveFinancialData();
            $financialMetrics = $this->calculateFinancialMetrics($financialData);
            $pengeluaranArray = $financialData['expenditures']->map(function ($item) {
                return [
                    'keperluan' => $item['keperluan'],
                    'nominal' => $item['nominal'],
                ];
            });

            $rasioLabaBersih = $this->formatPercentage($financialMetrics['profit'], $financialMetrics['totalPayment']);
            $rasioBeban = $this->formatPercentage($financialMetrics['totalExpenditure'], $financialMetrics['totalPayment']);

            $data = [];

            if ($financialMetrics['totalPayment'] && $pengeluaranArray) {
                $data =  [
                    'pendapatan' => $financialMetrics['totalPayment'],
                    'pengeluaran' => $pengeluaranArray,
                    'pengeluaran_total' => $financialMetrics['totalExpenditure'],
                    'laba_bersih' => $financialMetrics['profit'],
                    'rasio_laba_bersih' => $rasioLabaBersih,
                    'rasio_beban_operasional' => $rasioBeban,
                ];
            }

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
        $payments = PembayaranSiswa::whereBetween('created_at', [$this->startDate, $this->endDate])->get();
        $expenditures = Pengeluaran::whereBetween('disetujui_pada', [$this->startDate, $this->endDate])->paginate(20);
        return [
            'payments' => $payments,
            'expenditures' => $expenditures,
        ];
    }

    private function calculateFinancialMetrics(array $financialData)
    {
        // Hitung laba kotor, total pengeluaran, dan laba bersih
        $totalPayment = $financialData['payments']->sum('nominal');
        $totalExpenditure = Pengeluaran::whereBetween('disetujui_pada', [$this->startDate, $this->endDate])->sum('nominal');
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
        // biar keren
        if ($divisor) return ($value / $divisor) * 100;
        return $value > 0 ? 100.0 : 0.0;
        // if ($divisor === 0) {
        //     if ($value > 0) {
        //         return 100.0; // Jika ada pengeluaran, tampilkan 100%
        //     } else {
        //         return 0.0; // Jika tidak ada pengeluaran, tampilkan 0%
        //     }
        // } else {
        //     // Gunakan presisi yang cukup untuk menghindari pembulatan yang tidak diinginkan
        //     return ($value / $divisor) * 100;
        // }
    }
        public function getOptions()
    {
        $data = PembayaranSiswa::selectRaw('DISTINCT YEAR(created_at) as year, MONTHNAME(created_at) as month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc')
            ->get();

        $months = $data->pluck('month')->unique()->values()->toArray();
        $years = $data->pluck('year')->unique()->values()->toArray();

        // Create a mapping of month names to numbers
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

        // Format months with values and labels
        $formattedMonths = [];
        foreach ($months as $month) {
            $formattedMonths[] = [
                'values' => $monthNumbers[$month],
                'labels' => $month,
            ];
        }

        return response()->json([
            'months' => $formattedMonths,
            'years' => $years,
        ]);
    }

}
