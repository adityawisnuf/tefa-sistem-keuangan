<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = Carbon::now()->year;

        for ($i = 0; $i < 5; $i++) {
            // Random month and day for the year
            $randomDate = Carbon::create($currentYear + $i, rand(1, 12), rand(1, 28));

            DB::table('aset')->insert([
                [
                    'tipe' => 'Elektronik',
                    'nama' => 'Laptop',
                    'harga' => 15000000,
                    'kondisi' => 'Baru',
                    'penggunaan' => 'Digunakan untuk kegiatan operasional kantor.',
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate,
                ],
                [
                    'tipe' => 'Furnitur',
                    'nama' => 'Meja Kantor',
                    'harga' => 2000000,
                    'kondisi' => 'Bekas',
                    'penggunaan' => 'Dipakai di ruang rapat.',
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate,
                ],
                [
                    'tipe' => 'Kendaraan',
                    'nama' => 'Mobil Operasional',
                    'harga' => 250000000,
                    'kondisi' => 'Baik',
                    'penggunaan' => 'Mobil ini digunakan untuk transportasi luar kota.',
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate,
                ],
                [
                    'tipe' => 'Elektronik',
                    'nama' => 'Proyektor',
                    'harga' => 5000000,
                    'kondisi' => 'Baik',
                    'penggunaan' => 'Dipakai untuk presentasi di ruang rapat.',
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate,
                ],
                [
                    'tipe' => 'Bangunan',
                    'nama' => 'Gedung Kantor',
                    'harga' => 2000000000,
                    'kondisi' => 'Baik',
                    'penggunaan' => 'Digunakan sebagai kantor utama perusahaan.',
                    'created_at' => $randomDate,
                    'updated_at' => $randomDate,
                ],
            ]);
        }
    }
}
