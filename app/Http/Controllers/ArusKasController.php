<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\PembayaranKategori;
use App\Models\Pengeluaran;
use App\Models\PengeluaranKategori;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ArusKasController extends Controller
{
    public function index()
    {
        $payments = Pembayaran::paginate(50);
        $expenses = Pengeluaran::paginate(50);

        // Group payments by month and year
        $monthlyPayments = $payments->groupBy(function ($payment) {
            return Carbon::parse($payment->created_at)->format('F Y');
        })->map(function ($payments) {
            return $payments->map(function ($payment) {
                $category = PembayaranKategori::find($payment->pembayaran_kategori_id);
                return [
                    'nominal' => $payment->nominal,
                    'category' => $category->nama,
                ];
            });
        });

        // Group expenses by month and year
        $monthlyExpenses = $expenses->groupBy(function ($expense) {
            return Carbon::parse($expense->created_at)->format('F Y');
        })->map(function ($expenses) {
            return $expenses->map(function ($expense) {
                return [
                    'nominal' => $expense->nominal,
                    'category' => $expense->keperluan,
                ];
            });
        });

        // Calculate totals
        $totalIncome = $payments->sum('nominal');
        $totalExpense = $expenses->sum('nominal');

        return response()->json([
            'Pemasukan' => $monthlyPayments,
            'Pengeluaran' => $monthlyExpenses,
            'Total Pemasukan' => $totalIncome,
            'Total Pengeluaran' => $totalExpense,
            'Saldo Akhir' => $totalIncome - $totalExpense,
            'nextPageUrl' => $payments->nextPageUrl(),
        ]);
    }
}
