<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PembayaranKategori extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pembayaran_kategori = [
            [
                'nama' => 'PPDB',
                'jenis_pembayaran' => 2,
                'tanggal_pembayaran' => '01-07',
                'status' => 1
            ],
            [
                'nama' => 'Uang Seragam',
                'jenis_pembayaran' => 2,
                'tanggal_pembayaran' => '10-07',
                'status' => 1
            ],
            [
                'nama' => 'SPP',
                'jenis_pembayaran' => 1,
                'tanggal_pembayaran' => '11',
                'status' => 1
            ]
        ];

        foreach($pembayaran_kategori as $p){
            \App\Models\PembayaranKategori::create($p);
        }
    }
}
