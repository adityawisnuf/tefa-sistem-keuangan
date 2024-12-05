<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AsetSekolah;

class AsetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $asets = [
            [
                'tipe' => 'Peralatan Sekolah',
                'nama' => 'Meja Belajar',
                'harga' => 2000000,
                'kondisi' => 'Baik',
                'penggunaan' => 'Setiap hari saat jam pelajaran',
                'created_at' => now(),
            ],
            [
                'tipe' => 'Peralatan Sekolah',
                'nama' => 'Kursi Belajar',
                'harga' => 2000000,
                'kondisi' => 'Baik',
                'penggunaan' => 'Setiap hari saat jam pelajaran',
                'created_at' => now(),
            ]
        ];

        foreach($asets as $a){
            AsetSekolah::create($a);
        }
    }
}
