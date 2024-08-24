<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\AsetSekolah;
use App\Models\Pengeluaran;
use App\Models\PembayaranPpdb;
use App\Models\PembayaranSiswa;
use App\Models\PengeluaranKategori;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NeracaController extends Controller
{
    private function retrieveData()
    {
        // Inisialisasi query dasar
        $assets = AsetSekolah::query();
        $expenses = Pengeluaran::query();
        $liabilities = Pengeluaran::whereNull('disetujui_pada');
        $studentPayments = PembayaranSiswa::where('status', 1);
        $ppdbPayments = PembayaranPpdb::query();
        $approvedBudgets = Anggaran::where('status', 3);
        $receivables = PembayaranSiswa::where('status', 0);

        // Menjalankan query untuk mengambil data
        return [
            'assets' => $assets->get(),
            'expenses' => $expenses->get(),
            'liabilities' => $liabilities->get(),
            'studentPayments' => $studentPayments->get(),
            'ppdbPayments' => $ppdbPayments->get(),
            'approvedBudgets' => $approvedBudgets->get(),
            'receivables' => $receivables->get(),
        ];
    }

    public function index()
    {
        try {
            $data = $this->retrieveData();

            // Cek jika data kosong
            if ($data['assets']->isEmpty() && $data['expenses']->isEmpty() && $data['liabilities']->isEmpty()) {
                return response()->json(['message' => 'Tidak ada data untuk periode ini.'], 404);
            }

            $cash = $this->calculateCash($data['studentPayments'], $data['ppdbPayments']);
            $receivables = $this->formatReceivables($data['receivables']);
            $totalCurrentAssets = $cash + $receivables;
            $totalFixedAssets = $data['assets']->where('tipe', 'tetap')->sum('harga');
            $totalAssets = $totalFixedAssets + $totalCurrentAssets;

            $currentLiabilities = $this->calculateLiabilities($data['liabilities'], '1');
            $totalCurrentLiabilities = array_sum(array_column($currentLiabilities, 'value'));

            $longTermLiabilities = $this->calculateLiabilities($data['liabilities'], '2');
            $totalLongTermLiabilities = array_sum(array_column($longTermLiabilities, 'value'));

            $totalLiabilities = $totalCurrentLiabilities + $totalLongTermLiabilities;

            // Hitung ekuitas
            $equityData = $this->calculateEquity($data['studentPayments'], $data['ppdbPayments'], $data['approvedBudgets']);
            $totalEL = $totalLiabilities + $equityData['total_ekuitas'];

            $response = [
                'assets' => [
                    'current_assets' => [
                        [
                            'name' => 'Cash',
                            'value' => $this->formatCurrency($cash),
                        ],
                        [
                            'name' => 'Receivables',
                            'value' => $this->formatCurrency($receivables),
                        ]
                    ],
                    'total_current_assets' => $this->formatCurrency($totalCurrentAssets),
                    'fixed_assets' => $this->formatFixedAssets($data['assets'], 'tetap'),
                    'total_fixed_assets' => $this->formatCurrency($totalFixedAssets),
                    'total_assets' => $this->formatCurrency($totalAssets),
                ],
                'liabilities' => [
                    'current_liabilities' => $this->formatLiabilities($currentLiabilities),
                    'total_current_liabilities' => $this->formatCurrency($totalCurrentLiabilities),
                    'long_term_liabilities' => $this->formatLiabilities($longTermLiabilities),
                    'total_long_term_liabilities' => $this->formatCurrency($totalLongTermLiabilities),
                    'total_liabilities' => $this->formatCurrency($totalLiabilities),
                ],
                'equity' => [
                    'pendapatan' => $this->formatCurrency($equityData['pendapatan']),
                    'anggaran' => $this->formatCurrency($equityData['anggaran']),
                    'total_ekuitas' => $this->formatCurrency($equityData['total_ekuitas']),
                    'total_kewajiban_ekuitas' => $this->formatCurrency($totalEL),
                ],
            ];

            return response()->json(['data' => $response], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat memproses data.'], 500);
        }
    }

    private function formatFixedAssets($assets, $type)
    {
        // Memfilter dan memformat aset berdasarkan tipe
        return $assets->filter(function ($asset) use ($type) {
            return $asset->tipe === $type;
        })->map(function ($asset) {
            return [
                'name' => $asset->nama,
                'value' => $this->formatCurrency($asset->harga),
            ];
        })->toArray();
    }

    private function formatReceivables($receivables)
    {
        // Menghitung total piutang
        return $receivables->sum('nominal');
    }

    private function calculateCash($payments, $paymentsPpdb)
    {
        // Menghitung total kas
        $totalCash = $payments->sum('nominal') + $paymentsPpdb->sum('nominal');
        return $totalCash;
    }

    private function calculateLiabilities($liabilities, $type)
    {
        // Memfilter kewajiban berdasarkan tipe_utang dari PengeluaranKategori
        $filteredLiabilities = $liabilities->filter(function ($liability) use ($type) {
            $kategori = PengeluaranKategori::find($liability->pengeluaran_kategori_id);
            return $kategori && $kategori->tipe_utang === $type;
        });

        // Mengelompokkan kewajiban berdasarkan kategori dan menghitung total nilai
        $groupedLiabilities = $filteredLiabilities->groupBy(function ($liability) {
            return PengeluaranKategori::find($liability->pengeluaran_kategori_id)->nama;
        })->map(function ($items) {
            return $items->sum('nominal');
        });

        // Memformat response
        return $groupedLiabilities->map(function ($value, $name) {
            return [
                'name' => $name,
                'value' => $value,
            ];
        })->values()->toArray();
    }

    private function formatLiabilities($liabilities)
    {
        // Memformat nilai kewajiban dengan format rupiah
        return collect($liabilities)->map(function ($liability) {
            return [
                'name' => $liability['name'],
                'value' => $this->formatCurrency($liability['value']),
            ];
        })->toArray();
    }

    private function calculateEquity($payments, $pembayaranPpdb, $approvedBudgets)
    {
        // Menghitung total pendapatan dan ekuitas
        $totalIncome = $payments->sum('nominal') + $pembayaranPpdb->sum('nominal');
        $totalBudget = $approvedBudgets->sum('nominal');
        $equity = $totalIncome - $totalBudget;

        return [
            'pendapatan' => $totalIncome,
            'anggaran' => $totalBudget,
            'total_ekuitas' => $equity,
        ];
    }

    private function formatCurrency($value)
    {
        // Memformat nilai ke dalam format Rupiah
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
