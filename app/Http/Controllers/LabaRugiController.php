<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswa;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;

class LabaRugiController extends Controller
{
    public function index()
    {
        try {
            $financialData = $this->retrieveFinancialData();
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

    private function retrieveFinancialData()
    {
        // Mengambil data Pembayaran dan Pengeluaran
        $payments = PembayaranSiswa::all();
        $expenditures = Pengeluaran::all();
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
