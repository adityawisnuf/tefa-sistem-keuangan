<?php

namespace App\Http\Controllers;

use App\Models\Pembayaran;
use App\Models\PembayaranKategori;
use App\Models\PembayaranPpdb;
use App\Models\PembayaranSiswa;
use App\Models\Pengeluaran;
use App\Models\PengeluaranKategori;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
                        ->paginate(20);

        $paymentsPpdb = PembayaranPpdb::where('status', 1)
                            ->when($bulan, function ($query) use ($bulan) {
                                return $query->whereMonth('created_at', $bulan);
                            })
                            ->when($tahun, function ($query) use ($tahun) {
                                return $query->whereYear('created_at', $tahun);
                            })
                            ->paginate(20);

        $expenses = Pengeluaran::whereNotNull('disetujui_pada') // Tambahkan kondisi ini untuk menyaring pengeluaran yang belum disetujui
                            ->when($bulan, function ($query) use ($bulan) {
                                return $query->whereMonth('created_at', $bulan); // Sesuaikan dengan kolom yang benar jika diperlukan
                            })
                            ->when($tahun, function ($query) use ($tahun) {
                                return $query->whereYear('created_at', $tahun); // Sesuaikan dengan kolom yang benar jika diperlukan
                            })
                            ->paginate(20);


        // Prepare an array to hold the profit data
        $profit = [];

        // Combine and group payments by date and category
        foreach ($payments as $payment) {
            $kategori = PembayaranKategori::find(Pembayaran::find($payment->pembayaran_id)->pembayaran_kategori_id)->nama;
            $periode = Carbon::parse($payment->created_at)->format('d M Y');

            $key = $periode . '-' . $kategori;
            if (!isset($profit[$key])) {
                $profit[$key] = [
                    'tanggal' => $periode,
                    'keterangan' => $kategori,
                    'pemasukan' => 0,
                    'pengeluaran' => '-',
                ];
            }
            $profit[$key]['pemasukan'] += $payment->nominal;
        }

        // Group expenses
        foreach ($expenses as $expense) {
            $kategori = PengeluaranKategori::find($expense->pengeluaran_kategori_id)->nama;
            $periode = Carbon::parse($expense->disetujui_pada)->format('d M Y');

            $key = $periode . '-' . $kategori;
            if (!isset($profit[$key])) {
                $profit[$key] = [
                    'tanggal' => $periode,
                    'keterangan' => $kategori,
                    'pemasukan' => '-',
                    'pengeluaran' => 0,
                ];
            }
            $profit[$key]['pengeluaran'] += $expense->nominal;
        }

        // Group payment ppdb
        foreach ($paymentsPpdb as $ppdb) {
            $kategori = 'Bayaran Ppdb';
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
        $totalIncome = PembayaranSiswa::where('status', 1)->sum('nominal') + PembayaranPpdb::where('status', 1)->sum('nominal');
        $totalExpense = Pengeluaran::whereNotNull('disetujui_pada')->sum('nominal');
        $totalPaymentNow = $paymentsPpdb->sum('nominal') + $payments->sum('nominal');
        $totalExpensesNow = $expenses->sum('nominal');


        $total = [];
        if ($totalIncome > 0 || $totalExpense > 0) {
            $total = [
                'pemasukan' => number_format($totalIncome, 0, ',', '.'),
                'pengeluaran' => number_format($totalExpense, 0, ',', '.'),
                'pemasukan_sekarang' => number_format($totalPaymentNow, 0, ',', '.'),
                'pengeluaran_sekarang' => number_format($totalExpensesNow, 0, ',', '.'),
                'saldo_akhir'   => number_format($totalIncome - $totalExpense, 0, ',', '.')
            ];
        }
        foreach ($profit as &$item) {
            if ($item['pemasukan'] !== '-') {
                $item['pemasukan'] = number_format($item['pemasukan'], 0, ',', '.');
            }
            if ($item['pengeluaran'] !== '-') {
                $item['pengeluaran'] = number_format($item['pengeluaran'], 0, ',', '.');
            }
        }


        $data = [
            'profit' => $profit,
            'total' => $total,
        ];

        return response()->json(['data' => $data], 200);
    }
    public function getOptions()
    {

        // Gabungkan semua data
        $data = DB::table('pembayaran_siswa')
            ->selectRaw('YEAR(updated_at) as year, MONTHNAME(updated_at) as month')
            ->unionAll(
                DB::table('pembayaran_ppdb')->selectRaw('YEAR(created_at) as year, MONTHNAME(created_at) as month')
            )
            ->unionAll(
                DB::table('pembayaran')->selectRaw('YEAR(created_at) as year, MONTHNAME(created_at) as month')
            )
            ->unionAll(
                DB::table('pengeluaran')->selectRaw('YEAR(diajukan_pada) as year, MONTHNAME(diajukan_pada) as month')
            )
            ->unionAll(
                DB::table('pengeluaran')->selectRaw('YEAR(disetujui_pada) as year, MONTHNAME(disetujui_pada) as month')
            )
            ->groupBy('year', 'month')
            ->get();

        // Extract unique months and years
        $months = $data->pluck('month')->unique()->values()->toArray();
        $years = $data->pluck('year')->unique()->sortDesc()->values()->toArray();

        // Membuat mapping dari nama bulan ke angka bulan
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

        // Format bulan dengan values dan labels
        $formattedMonths = [];
        foreach ($months as $month) {
            // Check if the month exists in the mapping
            if (array_key_exists($month, $monthNumbers)) {
                $formattedMonths[] = [
                    'values' => $monthNumbers[$month],
                    'labels' => $month,
                ];
            } else {
                // Handle the case where the month is not found
                // You can log an error, return a default value, or ignore it
                // For example:
                error_log("Month not found: $month");
            }
        }

        return response()->json([
            'months' => $formattedMonths,
            'years' => $years,
        ]);
    }
}
