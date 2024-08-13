<?php

namespace App\Http\Controllers;

use App\Models\AsetSekolah;
use App\Models\PembayaranSiswa;
use Illuminate\Http\Request;

class NeracaController extends Controller
{
    public function index()
    {
        $asetSekolah = AsetSekolah::get();
        $pembayaranBelumSelesai = PembayaranSiswa::where('status', 0)->sum('nominal');

        $totalAktivaLancar = $pembayaranBelumSelesai;
        $totalAktivaTetap = $asetSekolah->sum('harga');
        $totalAktiva = $totalAktivaLancar + $totalAktivaTetap;

        if ($totalAktiva) {
            $aktiva = [
                'aktiva_lancar' => [
                    'piutang_siswa' => $pembayaranBelumSelesai
                ],
                'total_aktiva_lancar' => $totalAktivaLancar,
                'aktiva_tetap' => [
                    'aset_sekolah' => $asetSekolah
                ],
                'total_aktiva_tetap' => $totalAktivaTetap,
                'total_aktiva' => $totalAktiva
            ];
        } else {
            $aktiva = [];
        }

        $pasiva = [
            'kewajiban_lancar' => [],
            'total_kewajiban_lancar' => 0,
            'ekuitas' => [],
            'total_ekuitas' => 0,
            'total_pasiva' => 0,
        ];

        // Data untuk ditampilkan di view
        $data = [
            'aktiva' => $aktiva,
            'pasiva' => $pasiva
        ];

        return response()->json(['data' => $data]);
    }
}
