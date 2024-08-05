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
        $expenses = Pengeluaran::paginate(50);
        $payments = Pembayaran::paginate(50);

        $credits = [];
        $totalDebits = Pembayaran::sum('nominal');
        $totalCredits = Pengeluaran::sum('nominal');

        foreach ($expenses as $expense) {
            $expenseCategory = PengeluaranKategori::find($expense->pengeluaran_kategori_id);
            $credits[] = [
                'nominal' => $expense->nominal,
                'category' => $expenseCategory->nama,
            ];
        }

        $monthlyPayments = [];
        foreach ($payments as $payment) {
            $monthYear = Carbon::parse($payment->created_at)->format('F Y');
            if (!isset($monthlyPayments[$monthYear])) {
                $monthlyPayments[$monthYear] = [];
            }
            $monthlyPayments[$monthYear][] = [
                'nominal' => $payment->nominal,
                'category' => $payment->pembayaran_kategori->nama,
            ];
        }

        $yearlyPayments = [];
        foreach ($payments as $payment) {
            $year = Carbon::parse($payment->created_at)->format('Y');
            $paymentCategory = PembayaranKategori::find($payment->pembayaran_kategori_id);
            if (!isset($yearlyPayments[$year])) {
                $yearlyPayments[$year] = [];
            }
            $yearlyPayments[$year][] = [
                'nominal' => $payment->nominal,
                'category' => $paymentCategory->nama,
            ];
        }

        return response()->json([
            'Pemasukan Bayaran Bulanan' => $monthlyPayments,
            'Pemasukan Bayaran Tahunan' => $yearlyPayments,
            'kredit' => $credits,
            'Total Debet' => $totalDebits,
            'Total Kredit' => $totalCredits,
            'nextPageUrl' => $payments->nextPageUrl(),
        ]);
    }
}
