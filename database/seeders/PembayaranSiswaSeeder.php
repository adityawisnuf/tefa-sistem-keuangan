<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PembayaranSiswaSeeder extends Seeder
{
    protected $table = 'pembayaran_siswa';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {
            DB::table('pembayaran')->insert([
                'pembayaran_kategori_id' => $faker->numberBetween(1, 2), // Sesuaikan dengan jumlah kategori pembayaran
                'kelas_id' => 1, // Sesuaikan dengan jumlah kelas
                'siswa_id' => rand(11,20), // Sesuaikan dengan jumlah siswa
                'nominal' => $faker->numberBetween(100000, 5000000),
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
