<?php

namespace Database\Seeders;

use App\Models\PengeluaranKategori;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PengeluaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas = ['gaji guru', 'operasional', 'investasi', 'pembangunan', 'lainnya'];
        // tes commit

        foreach ($datas as $data) {
            PengeluaranKategori::create([
                'nama' => $data
            ]);
        }
    }
}
