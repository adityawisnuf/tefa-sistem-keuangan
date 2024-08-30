<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Anggaran; // Pastikan model Anggaran diimport
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    public function getMonitoringData()
    {
        // Ambil data dari metode getAnggaranColum di AnggaranController
        // Anda perlu membuat instance dari AnggaranController untuk memanggil metodenya

        $anggaranController = new AnggaranController();
        $anggaranData = $anggaranController->getAnggaranColum();
    
        return $anggaranData;
    }

    public function getAnggaranData(Request $request)
    {
        $period = $request->query('period', 'monthly');

        $diajukan = Anggaran::where('status', 1)->sum('nominal');
        $diapprove = Anggaran::where('status', 2)->sum('nominal');
        $total = $diajukan + $diapprove;
        $persentaseRealisasi = ($total > 0) ? $diapprove / $total * 100 : 0;

        $data = [
            'series' => [
                ($total > 0) ? $diajukan / $total * 100 : 0,
                ($total > 0) ? $diapprove / $total * 100 : 0,
                $persentaseRealisasi
            ],
            'labels' => ['Diajukan', 'Diapprove', 'Realisasi']
        ];

        return response()->json($data);
    }

    public function getAnggaranColum()
    {
        $keseluruhan = Anggaran::where('status', '!=', 3)->count();
        $terealisasi = Anggaran::where('status', 3)->count();
    
        $jumlahTerapproveTahunIni = Anggaran::whereYear('created_at', now()->year)
            ->where('status', 3)
            ->count();
        $jumlahTerapproveBulanIni = Anggaran::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('status', 3)
            ->count();
    
        $jumlahtahunini = Anggaran::selectRaw('MONTH(created_at) as month, COUNT(*) as count, SUM(nominal) as sum')
            ->whereYear('created_at', now()->year)
            ->where('status', 3)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    strtolower(now()->startOfYear()->addMonths($item->month - 1)->format('M')) => [
                        'count' => $item->count,
                        'sum of nominal' => $item->sum
                    ]
                ];
            });
    
        $jumlahbulanini = Anggaran::selectRaw('WEEK(created_at, 1) as week, COUNT(*) as count, SUM(nominal) as sum')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->where('status', 3)
            ->groupBy('week')
            ->orderBy('week')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    'minggu' . $item->week => [
                        'count' => $item->count,
                        'sum of nominal' => $item->sum
                    ]
                ];
            });
    
        $totalRencanaAnggaranTahunIni = Anggaran::whereYear('created_at', now()->year)
            ->sum('nominal');
        $totalRencanaAnggaranBulanIni = Anggaran::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('nominal');
    
        return response()->json([
            'total_keseluruhan' => $keseluruhan,
            'total_terealisasi' => $terealisasi,
            'jumlah_terapprove_tahun_ini' => $jumlahTerapproveTahunIni,
            'jumlah_terapprove_bulan_ini' => $jumlahTerapproveBulanIni,
            'jumlah_tahun_ini' => $jumlahtahunini,
            'jumlah_bulan_ini' => $jumlahbulanini,
            'total_rencana_anggaran_tahun_ini' => $totalRencanaAnggaranTahunIni,
            'total_rencana_anggaran_bulan_ini' => $totalRencanaAnggaranBulanIni,
        ]);
    }    
}
