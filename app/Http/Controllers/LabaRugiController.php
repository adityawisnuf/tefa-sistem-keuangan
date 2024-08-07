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
                $startDate = Carbon::createFromDate($now->year, 1, 1);
                $endDate = Carbon::createFromDate($now->year, 12, 31);
            }

            $financialData = $this->retrieveFinancialData($startDate, $endDate);
            $financialMetrics = $this->calculateFinancialMetrics($financialData);
            $pengeluaranArray = $financialData['expenditures']->map(function ($item) {
                return [
                    'keperluan' => $item['keperluan'],
                    'nominal' => $item['nominal'],
                ];
            });


            $response = [
                'pendapatan' => $financialMetrics['totalPayment'],
                'pengeluaran' => $pengeluaranArray,
                'pengeluaran_total' => $financialMetrics['totalExpenditure'],
                'laba_bersih' => $financialMetrics['profit'],
                'rasio_laba_bersih' => $this->formatPercentage($financialMetrics['profit'], $financialMetrics['totalPayment']),
                'rasio_beban_operasional' => $this->formatPercentage($financialMetrics['totalExpenditure'], $financialMetrics['totalPayment']),
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
        $payments = PembayaranSiswa::whereBetween('created_at', [$startDate, $endDate])->paginate(20);
        $expenditures = Pengeluaran::whereBetween('disetujui_pada', [$startDate, $endDate])->paginate(20);
        $payments = PembayaranSiswa::whereBetween('created_at', [$startDate, $endDate])->paginate(20);
        $expenditures = Pengeluaran::whereBetween('disetujui_pada', [$startDate, $endDate])->paginate(20);
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
        return [
            'totalPayment' => $totalPayment,
            'totalExpenditure' => $totalExpenditure,
            'profit' => $profit,
        ];
    }

    private function formatPercentage($value, $divisor)
    {
        if ($divisor === 0) {
            return '0%'; // Atau nilai lain yang sesuai, seperti 'N/A'
        } else {
            // Gunakan presisi yang cukup untuk menghindari pembulatan yang tidak diinginkan
            return number_format($value / $divisor * 100, 2) . '%';
        }
    }
}
