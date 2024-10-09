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
        try {
            $data = $this->retrieveData($request);

            return response()->json(['data' => $data], 200);
        } catch (\Exception $e) {
            // Log error
            logger()->error($e->getMessage());
            // Return error response
            return response()->json(['data' => 'Error terjadi kesalahan'], 500);
        }
    }

    private function retrieveData(Request $request)
{
    // Get the year from query parameters
    $tahun = $request->query('tahun');
    $bulan = $request->query('bulan');

    // Initialize arrays for each count (12 months)
    $countDiajukan = array_fill(0, 12, 0);
    $countDiapprove = array_fill(0, 12, 0);
    $countTerealisasikan = array_fill(0, 12, 0);
    $countGagal = array_fill(0, 12, 0);

    // Retrieve counts by month for each status
    $anggaranByMonth = Anggaran::when($tahun, function ($query) use ($tahun) {
        return $query->whereYear('tanggal_pengajuan', $tahun);
    })
        ->selectRaw('MONTH(tanggal_pengajuan) as month,
            COUNT(CASE WHEN status = 1 THEN 1 ELSE NULL END) as count_diajukan,
            COUNT(CASE WHEN status = 2 THEN 1 ELSE NULL END) as count_diapprove,
            COUNT(CASE WHEN status = 3 THEN 1 ELSE NULL END) as count_terealisasikan,
            COUNT(CASE WHEN status = 4 THEN 1 ELSE NULL END) as count_gagal')
        ->groupBy('month')
        ->get();

    // Populate the arrays for each month based on the retrieved data
    foreach ($anggaranByMonth as $data) {
        $monthIndex = $data->month - 1; // Month index (0 for January, 11 for December)
        $countDiajukan[$monthIndex] = $data->count_diajukan;
        $countDiapprove[$monthIndex] = $data->count_diapprove;
        $countTerealisasikan[$monthIndex] = $data->count_terealisasikan;
        $countGagal[$monthIndex] = $data->count_gagal;
    }

    // Example of how other anggaran data is handled (keep this part unchanged as per your example)
    $anggaran = Anggaran::when($bulan, function ($query) use ($bulan) {
        return $query->whereMonth('tanggal_pengajuan', $bulan);
    })
        ->when($tahun, function ($query) use ($tahun) {
            return $query->whereYear('tanggal_pengajuan', $tahun);
        })
        ->orderBy('tanggal_pengajuan', 'desc') // Menambahkan orderBy untuk mengurutkan dari tanggal terbaru
        ->get();
    // Format the anggaran data (you can keep your current mapping logic here)
    $anggaranFiltered = $anggaran->map(function ($item) {
        $item->tanggal_pengajuan = Carbon::parse($item->tanggal_pengajuan)->format('d M Y');
        $item->target_terealisasikan = $item->target_terealisasikan
            ? Carbon::parse($item->target_terealisasikan)->format('d M Y')
            : null;

        $item->nominal = $this->formatToRupiah($item->nominal);

        return $item->makeHidden(['id', 'deskripsi', 'created_at', 'updated_at']);
    });

    // Example of other sums, unchanged
    $totalDiajukan = $this->formatToRupiah(Anggaran::where('status', 1)->sum('nominal'));
    $totalDiapprove = $this->formatToRupiah(Anggaran::where('status', 2)->sum('nominal'));
    $totalTerealisasikan = $this->formatToRupiah(Anggaran::where('status', 3)->sum('nominal'));
    $totalGagal = $this->formatToRupiah(Anggaran::where('status', 4)->sum('nominal'));

    // Return the response
    return [
        'anggaran' => $anggaranFiltered,
        'total_anggaran_diajukan' => $totalDiajukan,
        'total_anggaran_diapprove' => $totalDiapprove,
        'total_anggaran_terealisasikan' => $totalTerealisasikan,
        'total_anggaran_gagal' => $totalGagal,
        'count_diajukan' => $countDiajukan,
        'count_diapprove' => $countDiapprove,
        'count_terealisasikan' => $countTerealisasikan,
        'count_gagal' => $countGagal,
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
