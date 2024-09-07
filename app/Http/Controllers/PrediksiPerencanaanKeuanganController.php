<?php

namespace App\Http\Controllers;

use App\Models\Anggaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        // Retrieve data with filtering based on the month and year if provided, and sort by tanggal_pengajuan
        $anggaran = Anggaran::when($bulan, function ($query) use ($bulan) {
            return $query->whereMonth('tanggal_pengajuan', $bulan);
        })
            ->when($tahun, function ($query) use ($tahun) {
                return $query->whereYear('tanggal_pengajuan', $tahun);
            })
            ->orderBy('tanggal_pengajuan', 'desc') // Menambahkan orderBy untuk mengurutkan dari tanggal terbaru
            ->get();
        $totalDiajukan = Anggaran::when($bulan, function ($query) use ($bulan) {
            return $query->whereMonth('tanggal_pengajuan', $bulan);
        })
            ->when($tahun, function ($query) use ($tahun) {
                return $query->whereYear('tanggal_pengajuan', $tahun);
            })
            ->where('status',1)
            ->orderBy('tanggal_pengajuan', 'desc') // Menambahkan orderBy untuk mengurutkan dari tanggal terbaru
            ->sum('nominal');
        $totalDiapprove = Anggaran::when($bulan, function ($query) use ($bulan) {
            return $query->whereMonth('tanggal_pengajuan', $bulan);
        })
            ->when($tahun, function ($query) use ($tahun) {
                return $query->whereYear('tanggal_pengajuan', $tahun);
            })
            ->where('status',2)
            ->orderBy('tanggal_pengajuan', 'desc') // Menambahkan orderBy untuk mengurutkan dari tanggal terbaru
            ->sum('nominal');
        $totalRealisasi = Anggaran::when($bulan, function ($query) use ($bulan) {
            return $query->whereMonth('tanggal_pengajuan', $bulan);
        })
            ->when($tahun, function ($query) use ($tahun) {
                return $query->whereYear('tanggal_pengajuan', $tahun);
            })
            ->where('status',3)
            ->orderBy('tanggal_pengajuan', 'desc') // Menambahkan orderBy untuk mengurutkan dari tanggal terbaru
            ->sum('nominal');
        $totalGagal = Anggaran::when($bulan, function ($query) use ($bulan) {
            return $query->whereMonth('tanggal_pengajuan', $bulan);
        })
            ->when($tahun, function ($query) use ($tahun) {
                return $query->whereYear('tanggal_pengajuan', $tahun);
            })
            ->where('status',4)
            ->orderBy('tanggal_pengajuan', 'desc') // Menambahkan orderBy untuk mengurutkan dari tanggal terbaru
            ->sum('nominal');

        // Format the data and remove unwanted fields
        $anggaranFiltered = $anggaran->map(function ($item) {
            $item->tanggal_pengajuan = Carbon::parse($item->tanggal_pengajuan)->format('d M Y');
            $item->target_terealisasikan = $item->target_terealisasikan
                ? Carbon::parse($item->target_terealisasikan)->format('d M Y')
                : null;

            $item->nominal = $this->formatToRupiah($item->nominal);

            return $item->makeHidden(['id', 'deskripsi', 'created_at', 'updated_at']);
        });

        return [
            'anggaran' => $anggaranFiltered,
            'total_anggaran_diajukan' => $this->formatToRupiah($totalDiajukan),
            'total_anggaran_diapprove' => $this->formatToRupiah($totalDiapprove),
            'total_anggaran_terealisasikan' => $this->formatToRupiah($totalRealisasi),
            'total_anggaran_gagal' => $this->formatToRupiah($totalGagal),
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

        return response()->json([
            'months' => $formattedMonths,
            'years' => $formattedYears,
        ]);
    }
}
