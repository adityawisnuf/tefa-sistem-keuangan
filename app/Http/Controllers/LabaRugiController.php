<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswa;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LabaRugiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $bulan = $request->query('bulan');
            $tahun = $request->query('tahun');

            // Buat tanggal awal dan akhir berdasarkan query
            $startDate = null;
            $endDate = null;

            if ($bulan && $tahun && $bulan != '') {
                $startDate = Carbon::createFromDate($tahun, $bulan, 1);
                $endDate = $startDate->copy()->endOfMonth();
            } elseif ($tahun) {
                $startDate = Carbon::createFromDate($tahun, 1, 1);
                $endDate = Carbon::createFromDate($tahun, 12, 31);
            } elseif (!$bulan && !$tahun) {
                // Jika tidak ada query bulan dan tahun, maka tampilkan data dari bulan dan tahun saat ini
                $now = Carbon::now();
                $startDate = $now->startOfMonth();
                $endDate = $startDate->copy()->endOfMonth();
            }

            $financialData = $this->retrieveFinancialData($startDate, $endDate);
            $financialMetrics = $this->calculateFinancialMetrics($financialData);
            $pengeluaranArray = $financialData['expenditures']->map(function ($item) {
                return [
                    'keperluan' => $item['keperluan'],
                    'nominal' => $item['nominal'],
                ];
            });

            $rasioLabaBersih = $this->formatPercentage($financialMetrics['profit'], $financialMetrics['totalPayment']);
            $rasioBeban = $this->formatPercentage($financialMetrics['totalExpenditure'], $financialMetrics['totalPayment']);

            $response = [
                'pendapatan' => $financialMetrics['totalPayment'],
                'pengeluaran' => $pengeluaranArray,
                'pengeluaran_total' => $financialMetrics['totalExpenditure'],
                'laba_bersih' => $financialMetrics['profit'],
                'rasio_laba_bersih' => $rasioLabaBersih,
                'rasio_beban_operasional' => $rasioBeban,
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            // Log error
            logger()->error($e->getMessage());
            // Return error response
            return response()->json(['error' => 'Terjadi kesalahan'], 500);
        }
    }

    private function retrieveFinancialData($startDate, $endDate)
    {
        // Mengambil data Pembayaran dan Pengeluaran
        $payments = PembayaranSiswa::whereBetween('created_at', [$startDate, $endDate])->get();
        $expenditures = Pengeluaran::whereBetween('disetujui_pada', [$startDate, $endDate])->get();
        return [
            'payments' => $payments,
            'expenditures' => $expenditures,
        ];
    }

    private function calculateFinancialMetrics(array $financialData)
    {
        // Hitung laba kotor, total pengeluaran, dan laba bersih
        $totalPayment = $financialData['payments']->sum('nominal');
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
        if ($divisor === 0) {
            if ($value > 0) {
                return 100.0; // Jika ada pengeluaran, tampilkan 100%
            } else {
                return 0.0; // Jika tidak ada pengeluaran, tampilkan 0%
            }
        } else {
            // Gunakan presisi yang cukup untuk menghindari pembulatan yang tidak diinginkan
            return ($value / $divisor) * 100;
        }
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
            'January' => 1,
            'February' => 2,
            'March' => 3,
            'April' => 4,
            'May' => 5,
            'June' => 6,
            'July' => 7,
            'August' => 8,
            'September' => 9,
            'October' => 10,
            'November' => 11,
            'December' => 12,
        ];
    
        // Add key-value pairs with month numbers
        $monthsWithNumbers = [];
        foreach ($months as $month) {
            $monthsWithNumbers[$monthNumbers[$month]] = $month;
        }
    
        return response()->json([
            'months' => $monthsWithNumbers,
            'years' => $years
        ]);
    }
}
