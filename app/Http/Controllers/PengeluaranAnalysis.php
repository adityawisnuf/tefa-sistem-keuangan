<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Pengeluaran;
use Illuminate\Http\Request;

class PengeluaranAnalysis extends Controller
{
    public function getPengeluaranPeriode($periode)
    {
        // Validasi periode
        if (!in_array($periode, ['harian', 'bulanan', 'tahunan'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid periode value',
            ], 422);
        }

        // Pengeluaran untuk periode saat ini
        $currentPeriod = $this->getPengeluaranForPeriod($periode, 'current');
        // Pengeluaran untuk periode sebelumnya
        $previousPeriod = $this->getPengeluaranForPeriod($periode, 'previous');

        // Hitung selisih dan persentase perubahan
        $difference = $currentPeriod - $previousPeriod;
        $percentageChange = $previousPeriod > 0 ? ($difference / $previousPeriod) * 100 : 0;

        // Tentukan apakah pengeluaran naik atau turun
        $trend = $difference > 0 ? 'naik' : ($difference < 0 ? 'turun' : 'tetap');

        return response()->json([
            'success' => true,
            'data' => [
                'pengeluaran_periode_saat_ini' => $currentPeriod,
                'pengeluaran_periode_sebelumnya' => $previousPeriod,
                'selisih' => $difference,
                'persentase_perubahan' => $percentageChange,
                'analisis_tren' => $trend,
            ]
        ]);
    }

    private function getPengeluaranForPeriod($periode, $type)
    {
        $query = Pengeluaran::where('status', Status::Accepted->value);

        switch ($periode) {
            case 'harian':
                if ($type === 'current') {
                    $query->whereDate('created_at', now()->toDateString());
                } else {
                    $query->whereDate('created_at', now()->subDay()->toDateString());
                }
                break;

            case 'bulanan':
                if ($type === 'current') {
                    $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                } else {
                    $query->whereMonth('created_at', now()->subMonth()->month)
                        ->whereYear('created_at', now()->subMonth()->year);
                }
                break;

            case 'tahunan':
                if ($type === 'current') {
                    $query->whereYear('created_at', now()->year);
                } else {
                    $query->whereYear('created_at', now()->subYear()->year);
                }
                break;
        }

        return $query->sum('nominal');
    }
}
