<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Buat data kelas
        $kelas = [
            [
                'sekolah_id' => 1,
                'jurusan' => 'IPA',
                'kelas' => 'X',
            ],
            [
                'sekolah_id' => 1,
                'jurusan' => 'IPA',
                'kelas' => 'XI',
            ],
            [
                'sekolah_id' => 1,
                'jurusan' => 'IPA',
                'kelas' => 'XII',
            ],
            [
                'sekolah_id' => 1,
                'jurusan' => 'IPS',
                'kelas' => 'X',
            ],
            [
                'sekolah_id' => 1,
                'jurusan' => 'IPS',
                'kelas' => 'XI',
            ],
            [
                'sekolah_id' => 1,
                'jurusan' => 'IPS',
                'kelas' => 'XII',
            ],
            [
                'sekolah_id' => 1,
                'jurusan' => 'Bahasa',
                'kelas' => 'X',
            ],
            [
                'sekolah_id' => 1,
                'jurusan' => 'Bahasa',
                'kelas' => 'XI',
            ],
            [
                'sekolah_id' => 1,
                'jurusan' => 'Bahasa',
                'kelas' => 'XII',
            ],
        ];

        foreach ($kelas as $data) {
            Kelas::create($data);
        }
    }
}
