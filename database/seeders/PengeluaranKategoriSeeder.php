<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengeluaranKategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas = ['Gaji Guru', 'Operasional', 'Investasi', 'Pembangunan', 'Lainnya'];

        foreach ($datas as $data) {
            DB::table('pengeluaran_kategori')->insert([
                'nama' => $data
            ]);
        }
    }
}
