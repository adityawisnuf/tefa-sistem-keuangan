<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswa;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;

class LabaRugiController extends Controller
{
    public function index(Request $request)
    {
        try {
            $month = $request->input('month', date('m')); // default bulan saat ini

            // Mendapatkan tanggal awal dan akhir bulan
            $startDate = date('Y-' . $month . '-01');
            $endDate = date('Y-' . $month . '-t');

            $financialData = $this->retrieveFinancialData($startDate, $endDate);
            $financialMetrics = $this->calculateFinancialMetrics($financialData);

            $response = [
                'pendapatan' => $financialMetrics['totalPayment'],
                'pengeluaran' => $financialData['expenditures']->mapWithKeys(function ($item) {
                    return [$item->keperluan => $item->nominal];
                }),
                'pengeluaran_total' => $financialMetrics['totalExpenditure'],
                'laba_bersih' => $financialMetrics['profit'],
                'rasio_laba_bersih' => $this->formatPercentage($financialMetrics['profit'] / $financialMetrics['totalPayment']),
                'rasio_beban_operasional' => $this->formatPercentage($financialMetrics['totalExpenditure'] / $financialMetrics['totalPayment']),
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
        $expenditures = Pengeluaran::whereBetween('created_at', [$startDate, $endDate])->get();
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

    private function formatPercentage($value)
    {
        return number_format($value * 100, 0) . '%';
    }
}
