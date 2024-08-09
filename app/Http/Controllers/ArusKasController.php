<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\PembayaranKategori;
use App\Models\PembayaranPpdb;
use App\Models\PembayaranSiswa;
use App\Models\Pengeluaran;
use App\Models\PengeluaranKategori;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ArusKasController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        $payments = PembayaranSiswa::where('status', 1)
                        ->when($bulan, function ($query) use ($bulan) {
                            return $query->whereMonth('created_at', $bulan);
                        })
                        ->when($tahun, function ($query) use ($tahun) {
                            return $query->whereYear('created_at', $tahun);
                        })
                        ->paginate();

        $paymentsPpdb = PembayaranPpdb::where('status', 1)
                            ->when($bulan, function ($query) use ($bulan) {
                                return $query->whereMonth('created_at', $bulan);
                            })
                            ->when($tahun, function ($query) use ($tahun) {
                                return $query->whereYear('created_at', $tahun);
                            })
                            ->paginate();

        $expenses = Pengeluaran::when($bulan, function ($query) use ($bulan) {
                            return $query->whereMonth('disetujui_pada', $bulan);
                        })
                        ->when($tahun, function ($query) use ($tahun) {
                            return $query->whereYear('disetujui_pada', $tahun);
                        })
                        ->paginate();

        // Prepare an array to hold the profit data
        $profit = [];

        // Combine and group payments
        foreach ($payments as $payment) {
            $kategori = PembayaranKategori::find(Pembayaran::find($payment->pembayaran_id)->pembayaran_kategori_id)->nama;
            $periode = Carbon::parse($payment->created_at)->format('d M Y');

            $profit[] = [
                'tanggal' => $periode,
                'keterangan' => $kategori,
                'pemasukan' => $payment->nominal,
                'pengeluaran' => 0,
            ];
        }

        // Group expenses
        foreach ($expenses as $expense) {
            $kategori = PengeluaranKategori::find($expense->pengeluaran_kategori_id)->nama;
            $periode = Carbon::parse($expense->disetujui_pada)->format('d M Y');

            $profit[] = [
                'tanggal' => $periode,
                'keterangan' => $kategori,
                'pemasukan' => '-',
                'pengeluaran' => $expense->nominal,
            ];
        }

        // Group payment ppdb
        foreach ($paymentsPpdb as $ppdb) {
            $kategori = 'Ppdb';
            $periode = Carbon::parse($ppdb->created_at)->format('d M Y');

            $profit[] = [
                'tanggal' => $periode,
                'keterangan' => $kategori,
                'pemasukan' => $ppdb->nominal,
                'pengeluaran' => '-',
            ];
        }

        // Sort the profit array by the date (in descending order)
        usort($profit, function($a, $b) {
            return strtotime($b['tanggal']) - strtotime($a['tanggal']);
        });

        // Calculate totals
        $totalIncome = PembayaranSiswa::all()->sum('nominal') + PembayaranPpdb::all()->sum('nominal');
        $totalExpense = Pengeluaran::all()->sum('nominal');

        $total = [];
        if ($totalIncome > 0 || $totalExpense > 0) {
            $total = [
                'total_pemasukan' => $totalIncome,
                'total_pengeluaran' => $totalExpense,
            ];
        }

        $data = [
            'profit' => $profit,
            'total' => $total,
        ];

        return response()->json(['data' => $data], 200);
    }
}
