<?php

namespace Database\Seeders;

use App\Models\KantinProduk;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KantinProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       KantinProduk::factory(10)->create();
    }
}
