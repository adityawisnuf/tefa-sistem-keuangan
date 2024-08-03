<?php

namespace App\Http\Controllers;

use App\Models\PembayaranSiswa;
use Illuminate\Http\Request;

class LabaRugiController extends Controller
{
    public function index()
    {

        $pembayaranSiswa = [
            [
                'id' => 1,
                'siswa_id' => 1,
                'kategori_id' => 1,
                'nominal' => 100000,
                'status' => 1
            ],
            [
                'id' => 2,
                'siswa_id' => 2,
                'kategori_id' => 1,
                'nominal' => 200000,
                'status' => 1
            ],
            [
                'id' => 2,
                'siswa_id' => 2,
                'kategori_id' => 2,
                'nominal' => 200000,
                'status' => 1
            ],
            [
                'id' => 3,
                'siswa_id' => 3,
                'kategori_id' => 3,
                'nominal' => 300000,
                'status' => 1
            ],
            [
                'id' => 1,
                'siswa_id' => 1,
                'kategori_id' => 1,
                'nominal' => 100000,
                'status' => 1
            ],
        ];

        $pengeluaran = [
            [
                'id' => 1,
                'pengeluaran_kategori_id' => 1,
                'keperluan' => 'Membeli spidol',
                'nominal' => 10000,
                'diajukan_pada' => now(),
                'disetujui_pada' => '',
            ],
            [
                'id' => 1,
                'pengeluaran_kategori_id' => 1,
                'keperluan' => 'Membeli pulpen',
                'nominal' => 10000,
                'diajukan_pada' => now(),
                'disetujui_pada' => '',
            ],
            [
                'id' => 2,
                'pengeluaran_kategori_id' => 1,
                'keperluan' => 'White Board',
                'nominal' => 100000,
                'diajukan_pada' => now(),
                'disetujui_pada' => '',
            ],
        ];

        $pendapatanTotal = array_sum(array_column($pembayaranSiswa, 'nominal'));

        $pengeluaranTotal = 0;
        foreach ($pengeluaran as $item) {
            $pengeluaranTotal += $item['nominal'];
        }

        $keperluan = array_column($pengeluaran, 'keperluan');
        $nominal = array_column($pengeluaran, 'nominal');

        $labaRugi = $pendapatanTotal - $pengeluaranTotal;

        $data = [
            'pendapatan' => $pendapatanTotal,
            'pengeluaran' => array_combine($keperluan, $nominal),
            'pengeluaran_total' => $pengeluaranTotal,
            'laba_bersih' => $labaRugi,
        ];

        return response()->json($data);
    }
}
