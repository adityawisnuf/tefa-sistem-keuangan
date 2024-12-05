<?php

namespace Database\Seeders;

use App\Models\Sekolah;
use App\Models\Kelas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SekolahKelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sekolah::create([
            'nama' => 'SMKN 1 Cianjur',
            'alamat' => 'JL. Siliwangi',
            'telepon' => '081234567890',
        ]);

        $kelases = [
            [
                'sekolah_id' => '1',
                'jurusan' => 'PPLG',
                'kelas' => 'X PPLG 1'
            ],
            [
                'sekolah_id' => '1',
                'jurusan' => 'PPLG',
                'kelas' => 'X PPLG 2'
            ],
            [
                'sekolah_id' => '1',
                'jurusan' => 'PPLG',
                'kelas' => 'X PPLG 3'
            ],
            [
                'sekolah_id' => '1',
                'jurusan' => 'PPLG',
                'kelas' => 'XI PPLG 1'
            ],
            [
                'sekolah_id' => '1',
                'jurusan' => 'PPLG',
                'kelas' => 'XI PPLG 2'
            ],
            [
                'sekolah_id' => '1',
                'jurusan' => 'PPLG',
                'kelas' => 'XI PPLG 3'
            ],
            [
                'sekolah_id' => '1',
                'jurusan' => 'PPLG',
                'kelas' => 'XII PPLG 1'
            ],
            [
                'sekolah_id' => '1',
                'jurusan' => 'PPLG',
                'kelas' => 'XII PPLG 2'
            ],
            [
                'sekolah_id' => '1',
                'jurusan' => 'PPLG',
                'kelas' => 'XII PPLG 3'
            ]
        ];

        foreach($kelases as $k){
            Kelas::create($k);
        }
    }
}
