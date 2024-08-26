<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\AsetSekolah;
use App\Models\Pengeluaran;
use App\Models\PembayaranPpdb;
use App\Models\PembayaranSiswa;
use App\Models\PengeluaranKategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NeracaController extends Controller
{
    private function retrieveData(Request $request)
    {
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        $assets = AsetSekolah::when($bulan, fn($query) => $query->whereMonth('created_at', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('created_at', $tahun));

        $expenses = Pengeluaran::when($bulan, fn($query) => $query->whereMonth('created_at', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('created_at', $tahun));

        $liabilities = Pengeluaran::whereNull('disetujui_pada')
            ->when($bulan, fn($query) => $query->whereMonth('diajukan_pada', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('diajukan_pada', $tahun));

        $studentPayments = PembayaranSiswa::where('status', 1)
            ->when($bulan, fn($query) => $query->whereMonth('created_at', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('created_at', $tahun));

        $ppdbPayments = PembayaranPpdb::when($bulan, fn($query) => $query->whereMonth('created_at', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('created_at', $tahun));

        $approvedBudgets = Anggaran::where('status', 3)
            ->when($bulan, fn($query) => $query->whereMonth('created_at', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('created_at', $tahun));

        $receivables = PembayaranSiswa::where('status', 0)
            ->when($bulan, fn($query) => $query->whereMonth('created_at', $bulan))
            ->when($tahun, fn($query) => $query->whereYear('created_at', $tahun));

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

    public function index(Request $request)
    {
        try {
            $data = $this->retrieveData($request);

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

            $currentAktiva = [
                ['name' => 'Cash', 'value' => $this->formatCurrency($cash)],
                ['name' => 'Receivables', 'value' => $this->formatCurrency($receivables)]
            ];

            $equityData = $this->calculateEquity($data['studentPayments'], $data['ppdbPayments'], $data['approvedBudgets']);
            $totalEL = $totalLiabilities + $equityData['total_ekuitas'];

            $ekuitas = [
                ['name' => 'pendapatan', 'value' => $this->formatCurrency($equityData['pendapatan'])],
                ['name' => 'anggaran', 'value' => $this->formatCurrency($equityData['anggaran'])],
            ];

            $response = [
                'aktiva_lancar' => $currentAktiva,
                'total_aktiva_lancar' => $this->formatCurrency($totalCurrentAssets),
                'aktiva_tetap' => $this->formatFixedAssets($data['assets'], 'tetap'),
                'total_aktiva_tetap' => $this->formatCurrency($totalFixedAssets),
                'kewajiban_lancar' => $this->formatLiabilities($currentLiabilities),
                'total_kewajiban_lancar' => $this->formatCurrency($totalCurrentLiabilities),
                'kewajiban_jangka_panjang' => $this->formatLiabilities($longTermLiabilities),
                'total_kewajiban_jangka_panjang' => $this->formatCurrency($totalLongTermLiabilities),
                'ekuitas' => $ekuitas,
                'total_ekuitas' => $this->formatCurrency($equityData['total_ekuitas']),
                'total_aktiva' => $this->formatCurrency($totalAssets),
                'total_pasiva' => $this->formatCurrency($totalLiabilities),
                'total_pasiva_ekuitas' => $this->formatCurrency($totalEL),
            ];

            return response()->json(['data' => $response], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat memproses data.'], 500);
        }
    }

    private function formatFixedAssets($assets, $type)
    {
        return $assets->filter(fn($asset) => $asset->tipe === $type)
            ->map(fn($asset) => ['name' => $asset->nama, 'value' => $this->formatCurrency($asset->harga)])
            ->toArray();
    }

    private function formatReceivables($receivables)
    {
        return $receivables->sum('nominal');
    }

    private function calculateCash($payments, $paymentsPpdb)
    {
        return $payments->sum('nominal') + $paymentsPpdb->sum('nominal');
    }

    private function calculateLiabilities($liabilities, $type)
    {
        $filteredLiabilities = $liabilities->filter(function ($liability) use ($type) {
            $kategori = PengeluaranKategori::find($liability->pengeluaran_kategori_id);
            return $kategori && $kategori->tipe_utang === $type;
        });

        $groupedLiabilities = $filteredLiabilities->groupBy(fn($liability) => PengeluaranKategori::find($liability->pengeluaran_kategori_id)->nama)
            ->map(fn($items) => $items->sum('nominal'));

        return $groupedLiabilities->map(fn($value, $name) => ['name' => $name, 'value' => $value])
            ->values()->toArray();
    }

    private function formatLiabilities($liabilities)
    {
        return collect($liabilities)->map(fn($liability) => ['name' => $liability['name'], 'value' => $this->formatCurrency($liability['value'])])
            ->toArray();
    }

    private function calculateEquity($payments, $pembayaranPpdb, $approvedBudgets)
    {
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
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    public function getOptions()
    {
        $data = DB::table('pembayaran_siswa')
            ->selectRaw('YEAR(updated_at) as year, MONTHNAME(updated_at) as month')
            ->unionAll(
                DB::table('pembayaran_ppdb')->selectRaw('YEAR(created_at) as year, MONTHNAME(created_at) as month')
            )
            ->unionAll(
                DB::table('anggaran')->selectRaw('YEAR(created_at) as year, MONTHNAME(created_at) as month')
            )
            ->unionAll(
                DB::table('aset')->selectRaw('YEAR(created_at) as year, MONTHNAME(created_at) as month')
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

        return response()->json($data);
    }
}
