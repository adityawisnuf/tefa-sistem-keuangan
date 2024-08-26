<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class PrediksiPerencanaanKeuanganController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->retrieveData($request);

        return response()->json(['data' => $data], 200);
    }

    private function retrieveData(Request $request)
    {
        // Get the month and year from query parameters
        $bulan = $request->query('bulan');
        $tahun = $request->query('tahun');

        // Retrieve data with filtering based on the month and year if provided
        $anggaran = Anggaran::when($bulan, function ($query) use ($bulan) {
                return $query->whereMonth('tanggal_pengajuan', $bulan);
            })
            ->when($tahun, function ($query) use ($tahun) {
                return $query->whereYear('tanggal_pengajuan', $tahun);
            })
            ->get();

        // Format the data and remove unwanted fields
        $anggaranFiltered = $anggaran->map(function ($item) {
            $item->tanggal_pengajuan = Carbon::parse($item->tanggal_pengajuan)->format('d M Y');
            $item->target_terealisasikan = $item->target_terealisasikan
                ? Carbon::parse($item->target_terealisasikan)->format('d M Y')
                : null;

            return $item->makeHidden(['id', 'deskripsi', 'created_at', 'updated_at']);
        });

        return [
            'anggaran' => $anggaranFiltered,
            'total_anggaran_diajukan' => $this->formatToRupiah(Anggaran::all()->where('status', 1)->sum('nominal')),
            'total_anggaran_diapprove' => $this->formatToRupiah(Anggaran::all()->where('status', 2)->sum('nominal')),
            'total_anggaran_terealisasikan' => $this->formatToRupiah(Anggaran::all()->where('status', 3)->sum('nominal')),
            'total_anggaran_gagal' => $this->formatToRupiah(Anggaran::all()->where('status', 4)->sum('nominal')),
            'count_diajukan' => Anggaran::all()->where('status', 1)->count(),
                'count_diapprove' => Anggaran::all()->where('status', 2)->count(),
                'count_terealisasikan' => Anggaran::all()->where('status', 3)->count(),
                'count_gagal' => Anggaran::all()->where('status', 4)->count(),
            ];
    }

    private function formatToRupiah($value)
    {
        // Format the value into Rupiah format
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
    public function getOptions()
    {

        // Gabungkan semua data
        $data = DB::table('anggaran')
            ->selectRaw('YEAR(created_at) as year, MONTHNAME(created_at) as month')
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
