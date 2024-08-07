<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\PembayaranKategori;
use App\Models\PembayaranSiswa;
use App\Models\Pengeluaran;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ArusKasController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        $payments = PembayaranSiswa::when($bulan, function ($query) use ($bulan) {
                            return $query->whereMonth('created_at', $bulan);
                        })
                        ->when($tahun, function ($query) use ($tahun) {
                            return $query->whereYear('created_at', $tahun);
                        })
                        ->get();

        $expenses = Pengeluaran::when($bulan, function ($query) use ($bulan) {
                            return $query->whereMonth('created_at', $bulan);
                        })
                        ->when($tahun, function ($query) use ($tahun) {
                            return $query->whereYear('created_at', $tahun);
                        })
                        ->get();

        // Group payments by month and year
        $monthlyPayments = $payments->groupBy(function ($payment) {
            return Carbon::parse($payment->created_at)->format('F Y');
        })->map(function ($payments) {
            return $payments->map(function ($payment) {
                $categories = Pembayaran::find($payment->pembayaran_id);
                $category = PembayaranKategori::find($categories->pembayaran_kategori_id);
                return [
                    'payment' => $payment->nominal,
                    'category' => $category->nama,
                    'tanggal' => Carbon::parse($payment->created_at)->format('d M Y'),
                ];
            });
        });

        // Group expenses by month and year
        $monthlyExpenses = $expenses->groupBy(function ($expense) {
            return Carbon::parse($expense->disetujui_pada)->format('F Y');
        })->map(function ($expenses) {
            return $expenses->map(function ($expense) {
                return [
                    'expense' => $expense->nominal,
                    'category' => $expense->keperluan,
                    'tanggal' => Carbon::parse($expense->disetujui_pada)->format('d M Y'),
                ];
            });
        });

        // Calculate totals
        $totalIncome = $payments->sum('nominal');
        $totalExpense = $expenses->sum('nominal');

        $data = [
            'Pemasukan' => $monthlyPayments,
            'Pengeluaran' => $monthlyExpenses,
            'Total Pemasukan' => $totalIncome,
            'Total Pengeluaran' => $totalExpense,
            'Saldo Akhir' => $totalIncome - $totalExpense,
        ];
        return response()->json($data);
    }
}
