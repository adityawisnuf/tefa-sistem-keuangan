<?php

namespace Database\Seeders;

use App\Models\Ppdb;
use App\Models\Pendaftar;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PPDBSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ppdb::create([
            'status' => 1,
            'merchant_order_id' => 'ORD123456789',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
