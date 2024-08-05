<?php

namespace Database\Seeders;

use App\Models\Sekolah;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sekolah = [
            [
                'nama' => 'Sekolah Menengah Atas Negeri 1',
                'alamat' => 'Jl. Raya Jakarta, No. 123',
                'telepon' => '021-1234567',
            ],
            [
                'nama' => 'Sekolah Menengah Atas Negeri 2',
                'alamat' => 'Jl. Raya Bandung, No. 456',
                'telepon' => '022-9876543',
            ],
        ];

        foreach ($sekolah as $data) {
            Sekolah::create($data);
        }

        
    }
}
