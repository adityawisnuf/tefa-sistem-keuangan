<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KantinProdukKategoriSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\KantinProdukKategori::factory(10)->create();

        $kantinprodukkategori = [
            [
                'nama_kategori' => 'Makanan Berat',
                'deskripsi' => 'Makanan berat untuk sarapan, makan siang, makan sore',
            ],
            [
                'nama_kategori' => 'Makanan Ringan',
                'deskripsi' => 'Makanan ringan seperti cemilan, snack, keripik, dll.',
            ],
            [
                'nama_kategori' => 'Minuman',
                'deskripsi' => 'Minuman hangat dan dingin yang menyehatkan',
            ],
        ];

        foreach($kantinprodukkategori as $k){
            \App\Models\KantinProdukKategori::create($k);
        }
    }
}