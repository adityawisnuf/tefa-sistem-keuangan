<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Database\Seeders\PengeluaranSeeder as SeedersPengeluaranSeeder;
use Illuminate\Database\Seeder;
use PengeluaranSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            IndoRegionSeeder::class,
            AmountSeeder::class,
            SekolahKelasSeeder::class,
            SiswaOrtuSeeder::class,
            KantinProdukKategoriSeeder::class,
            KantinSeeder::class,
            KantinProdukSeeder::class,
            LaundrySeeder::class,
            LaundryLayananSeeder::class,
            PembayaranKategori::class,
            PembayaranSeeder::class,
            PengeluaranKategoriSeeder::class,
            SeedersPengeluaranSeeder::class,
            AnggaranSeeder::class,
            AsetSeeder::class
        ]);
    }
}