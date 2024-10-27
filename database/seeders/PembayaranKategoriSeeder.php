<?php

namespace Database\Seeders;

use App\Models\PembayaranKategori;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PembayaranKategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PembayaranKategori::create([
            'nama' => 'SPP',
            'jenis_pembayaran' => 1,
            'tanggal_pembayaran' => '01',
            'status' => 1
        ]);
        PembayaranKategori::create([
            'nama' => 'Tahunan',
            'jenis_pembayaran' => 2,
            'tanggal_pembayaran' => '01-01',
            'status' => 1
        ]);
    }
}
