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
        \App\Models\PembayaranKategori::create([
            'nama' => 'ppdb',
            'jenis_pembayaran' => 1,
            'tanggal_pembayaran' => now(),
            'status' => 1
        ]);
    }
}
