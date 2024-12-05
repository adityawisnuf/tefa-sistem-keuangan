<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PengeluaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (range(1, Carbon::now()->month) as $month) {
            DB::table('pengeluaran')->insert([
                'pengeluaran_kategori_id' => 1,
                'keperluan' => 'Gaji Guru bulan '.$month,
                'nominal' => rand(500000, 5000000),
                'diajukan_pada' => Carbon::create(Carbon::now()->year, $month, rand(1, 28)),
                'disetujui_pada' => Carbon::create(Carbon::now()->year, $month, rand(1, 28)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        $categories = DB::table('pengeluaran_kategori')->get();

        foreach($categories as $category){
            if($category->id !== 1){
                DB::table('pengeluaran')->insert([
                    'pengeluaran_kategori_id' => $category->id,
                    'keperluan' => $category->nama.'1',
                    'nominal' => rand(500000, 5000000),
                    'diajukan_pada' => Carbon::create(Carbon::now()->year, $month, rand(1, 28)),
                    'disetujui_pada' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
