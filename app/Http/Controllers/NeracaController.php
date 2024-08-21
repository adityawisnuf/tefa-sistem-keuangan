<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use App\Models\AsetSekolah;
use App\Models\Pengeluaran;
use App\Models\PembayaranPpdb;
use App\Models\PembayaranSiswa;
use App\Models\PengeluaranKategori;
use Illuminate\Http\Request;

class NeracaController extends Controller
{
    private function retrieveData()
    {
        // Retrieve all assets data
        $assets = AsetSekolah::all();

        // Retrieve all expenses data
        $expenses = Pengeluaran::get();

        // Retrieve all liabilities data (belum disetujui)
        $liabilities = Pengeluaran::whereNull('disetujui_pada')->get();

        // Retrieve all payments data
        $payments = PembayaranSiswa::where('status', 1)->get();

        // Retrieve all PPDB payments data
        $paymentsPpdb = PembayaranPpdb::get();

        // Retrieve approved budget data
        $approvedBudgets = Anggaran::where('status', 3)->get();

        // Retrieve all unpaid student payments as receivables
        $receivables = PembayaranSiswa::where('status', 0)->get();

        // Format the data into an array
        $data = [
            'assets' => $assets,
            'expenses' => $expenses,
            'liabilities' => $liabilities,
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

        $cash = $this->calculateCash($data['payments'], $data['paymentsPpdb']);
        $receivables = $this->formatReceivables($data['receivables']);
        $tca = $cash + $receivables;
        $tfe = $this->retrieveData()['assets']->sum('harga');

        // Format the response to match accounting standards and frontend requirements
        $response = [
            'assets' => [
                'aset_lancar' => [
                    'kas' => $cash,
                    'piutang' => $receivables,
                ],
                'total_aset_lancar' => $tca,
                'aset_tetap' => $this->formatAssets($data['assets'], 'tetap'),
                'total_aset_tetap' => $tfe,
                'total_aset' => $tca + $tfe,
            ],
            'kewajiban' => [
                'kewajiban_lancar' => $this->calculateLiabilities($data['liabilities'], '1'),
                'kewajiban_jangka_panjang' => $this->calculateLiabilities($data['liabilities'], '2'),
            ],
            'ekuitas' => $this->calculateEquity($data['payments'], $data['paymentsPpdb'], $data['approvedBudgets']),
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

    private function calculateLiabilities($liabilities, $type)
    {
        // Filter liabilities by tipe_utang from PengeluaranKategori
        $filteredLiabilities = $liabilities->filter(function ($liability) use ($type) {
            $kategori = PengeluaranKategori::find($liability->pengeluaran_kategori_id);
            return $kategori && $kategori->tipe_utang === $type;
        });

        // Group liabilities by kategori and calculate total value
        $groupedLiabilities = $filteredLiabilities->groupBy(function ($liability) {
            return PengeluaranKategori::find($liability->pengeluaran_kategori_id)->nama;
        })->map(function ($items) {
            return $items->sum('nominal');
        });

        // Format the response
        return $groupedLiabilities->map(function ($value, $name) {
            return [
                'name' => $name,
                'value' => $value,
            ];
        })->values()->toArray();
    }

    private function calculateEquity($payments, $pembayaranPpdb, $approvedBudgets)
    {
        // Calculate total income and equity
        $totalIncome = $payments->sum('nominal') + $pembayaranPpdb->sum('nominal');
        $totalBudget = $approvedBudgets->sum('nominal');
        $equity = $totalIncome - $totalBudget;

        return [
            'total_ekuitas' => $equity,
            'pendapatan' => $totalIncome,
            'anggaran' => $totalBudget,
        ];
    }
}
