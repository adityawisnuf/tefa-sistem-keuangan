<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\AsetSekolah;
use App\Models\Pengeluaran;
use App\Models\PembayaranPpdb;
use App\Models\PembayaranSiswa;
use App\Models\Siswa;
use Illuminate\Http\Request;

class NeracaController extends Controller
{
    private function retrieveData()
    {
        // Retrieve all assets data
        $assets = AsetSekolah::all();

        // Retrieve all expenses data
        $expenses = Pengeluaran::get();

        // Retrieve all payments data
        $payments = PembayaranSiswa::where('status', 1)->get();

        // Retrieve all payments data
        $paymentsPpdb = PembayaranPpdb::get();

        // Retrieve approved budget data
        $approvedBudgets = Anggaran::where('status', 'approved')->get();

        // Retrieve all unpaid student payments as receivables
        $receivables = PembayaranSiswa::where('status', 0)->get();

        // Format the data into an array
        $data = [
            'assets' => $assets,
            'expenses' => $expenses,
            'payments' => $payments,
            'paymentsPpdb' => $paymentsPpdb,
            'approvedBudgets' => $approvedBudgets,
            'receivables' => $receivables,
        ];

        return $data;
    }

    public function index()
    {
        // Retrieve the data using the private function
        $data = $this->retrieveData();

        // Format the response to match accounting standards and frontend requirements
        $response = [
            'assets' => [
                'current_assets' => [
                    'cash' => $this->calculateCash($data['payments'], $data['paymentsPpdb']),
                    'receivables' => $this->formatReceivables($data['receivables']),
                ],
                'fixed_assets' => $this->formatAssets($data['assets'], 'tetap'),
            ],
            'liabilities' => [
                'current_liabilities' => $this->calculateLiabilities($data['expenses'], 'current'),
                'long_term_liabilities' => $this->calculateLiabilities($data['expenses'], 'long_term'),
            ],
            'equity' => $this->calculateEquity($data['payments'], $data['approvedBudgets']),
        ];

        return response()->json(['data' => $response], 200);
    }

    private function formatAssets($assets, $type)
    {
        // Filter assets by type and format them
        return $assets->filter(function ($asset) use ($type) {
            return $asset->tipe === $type;
        })->map(function ($asset) {
            return [
                'name' => $asset->nama,
                'value' => $asset->harga,
            ];
        })->toArray();
    }

    private function formatReceivables($receivables)
    {
        return $receivables->sum('nominal');
    }

    private function calculateCash($payments, $paymentsPpdb)
    {
        // Calculate the total cash
        $totalCash = $payments->sum('nominal') + $paymentsPpdb->sum('nominal');
        return $totalCash;
    }

    private function calculateLiabilities($expenses, $type)
    {
        // Filter expenses by type and calculate the total liabilities
        $filteredExpenses = $expenses->filter(function ($expense) use ($type) {
            return $expense->tipe === $type;
        });

        $totalLiabilities = $filteredExpenses->sum('nominal');

        return [
            'total' => $totalLiabilities,
            'details' => $filteredExpenses->map(function ($expense) {
                return [
                    'nama' => $expense->nama,
                    'nominal' => $expense->nominal,
                ];
            })->toArray(),
        ];
    }

    private function calculateEquity($payments, $approvedBudgets)
    {
        // Calculate total income and equity
        $totalIncome = $payments->sum('nominal');
        $totalBudget = $approvedBudgets->sum('nominal');
        $equity = $totalIncome - $totalBudget;

        return [
            'total_equity' => $equity,
            'income' => $totalIncome,
            'budget' => $totalBudget,
        ];
    }
}
