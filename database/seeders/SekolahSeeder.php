<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Sekolah::create([
            'nama' => 'SMKN 1 Cianjur',
            'alamat' => 'JL.Siliwangi',
            'telepon' => '08123456789',
        ]);
    }
}
