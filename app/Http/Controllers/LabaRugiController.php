<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\AsetSekolah;
use App\Models\PembayaranPpdb;
use App\Models\PembayaranSiswa;
use App\Models\PembayaranSiswaCicilan;
use App\Models\Pengeluaran;
use App\Models\PengeluaranKategori;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabaRugiController extends Controller
{
    protected $startDate;
    protected $endDate;

    public function index(Request $request)
    {
        try {
            $bulan = $request->query('bulan');
            $tahun = $request->query('tahun');

            // Set tanggal mulai dan akhir
            $this->startDate = Carbon::createFromDate($tahun ?? date('Y'), $bulan ?? date('m'), 1);
            $this->endDate = $this->startDate->copy()->endOfMonth();

            if ($tahun || !$tahun && !$bulan) {
                $this->startDate = Carbon::createFromDate($tahun, 1, 1);
                $this->endDate = Carbon::createFromDate($tahun, 12, 31);
            }

            $financialData = $this->retrieveFinancialData();
            $financialMetrics = $this->calculateFinancialMetrics($financialData);

            // Ambil kategori pengeluaran
            $categories = PengeluaranKategori::pluck('nama', 'id')->toArray();

            // Kelompokkan pengeluaran berdasarkan kategori
            $pengeluaranByCategory = $financialData['expenditures']->groupBy('pengeluaran_kategori_id')->map(function ($items) use ($categories) {
                $kategoriId = $items->first()->pengeluaran_kategori_id;
                return [
                    'keperluan' => $categories[$kategoriId] ?? 'Tidak Diketahui',
                    'nominal' => $this->formatToRupiah($items->sum('nominal'))
                ];
            })->values();

            // Hitung rasio laba bersih dan beban operasional
            $rasioLabaBersih = $this->formatPercentage($financialMetrics['profit'], $financialMetrics['totalPayment']);
            $rasioBeban = $this->formatPercentage($financialMetrics['totalExpenditure'], $financialMetrics['totalPayment']);

            // Jika tidak ada data, kembalikan data kosong
            if (!$financialMetrics['totalPayment'] && $pengeluaranByCategory->isEmpty()) {
                return response()->json(['data' => []], 200);
            }

            $data = [
                'pendapatan' => $this->formatToRupiah($financialMetrics['totalPayment']),
                'pengeluaran' => $pengeluaranByCategory,
                'pengeluaran_total' => $this->formatToRupiah($financialMetrics['totalExpenditure']),
                'laba_bersih' => $this->formatToRupiah($financialMetrics['profit']),
                'rasio_laba_bersih' => $rasioLabaBersih,
                'rasio_beban_operasional' => $rasioBeban,
            ];

            return response()->json(['data' => $data], 200);
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
        $payments = PembayaranSiswa::whereBetween('updated_at', [$this->startDate, $this->endDate])
            ->where('status', 1)
            ->get();
        $cicilanPayments = PembayaranSiswaCicilan::whereBetween('updated_at', [$this->startDate, $this->endDate])
            ->get();
        $paymentsPpdb = PembayaranPpdb::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 1)
            ->get();
        $expenditures = Pengeluaran::whereBetween('disetujui_pada', [$this->startDate, $this->endDate])
            ->get();

        return [
            'payments' => $payments,
            'paymentsPpdb' => $paymentsPpdb,
            'cicilanPayments' => $cicilanPayments,
            'expenditures' => $expenditures,
        ];
    }

    private function calculateFinancialMetrics(array $financialData)
    {
        $totalPayment = $financialData['payments']->sum('nominal') + $financialData['paymentsPpdb']->sum('nominal') + $financialData['cicilanPayments']->sum('nominal_cicilan');
        $totalExpenditure = $financialData['expenditures']->sum('nominal');
        $profit = $totalPayment - $totalExpenditure;

        return [
            // 'cicilanPayments' => $financialData['cicilanPayments']->sum('nominal_cicilan'),
            // 'payments' => $financialData['payments']->sum('nominal'),
            // 'paymentsPpdb' => $financialData['paymentsPpdb']->sum('nominal'),
            'totalPayment' => $totalPayment,
            'totalExpenditure' => $totalExpenditure,
            'profit' => $profit,
        ];
    }

    private function formatPercentage($value, $divisor)
    {
        if ($divisor) return ($value / $divisor) * 100;
        return $value > 0 ? 100.0 : 0.0;
    }

    private function formatToRupiah($value)
    {
        // Memformat nilai ke dalam format Rupiah
        return 'Rp ' . number_format($value, 0, ',', '.');
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
                DB::table('pengeluaran')->selectRaw('YEAR(diajukan_pada) as year, MONTHNAME(diajukan_pada) as month')
            )
            ->unionAll(
                DB::table('pengeluaran')->selectRaw('YEAR(disetujui_pada) as year, MONTHNAME(disetujui_pada) as month')
            )
            ->groupBy('year', 'month')
            ->get();

        $data = $data->filter(function ($item) {
            return !is_null($item->year) && !is_null($item->month);
        });
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
            if (array_key_exists($month, $monthNumbers)) {
                $formattedMonths[] = [
                    'values' => $monthNumbers[$month],
                    'labels' => $month,
                ];
            } else {
                error_log("Month not found: $month");
            }
        }

        // Format tahun dengan values dan labels
        $formattedYears = [];
        foreach ($years as $year) {
            $formattedYears[] = [
                'values' => (string) $year,
                'labels' => (string) $year,
            ];
        }

        // Tambahkan opsi "semua" di awal list bulan dan tahun
        array_unshift($formattedMonths, [
            'values' => '',
            'labels' => 'Semua',
        ]);

        array_unshift($formattedYears, [
            'values' => '',
            'labels' => 'Semua',
        ]);


        return response()->json([
            'months' => $formattedMonths,
            'years' => $formattedYears,
        ]);
    }
}
