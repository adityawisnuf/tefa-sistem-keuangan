<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\PengeluaranKategori;

class PengeluaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas = ['gaji guru', 'operasional', 'investasi', 'pembangunan', 'lainnya'];
        // tes commit

        foreach ($datas as $data) {
            PengeluaranKategori::create([
                'nama' => $data
            ]);
        }

        $categories = DB::table('pengeluaran_kategori')->pluck('id')->toArray();

        foreach (range(1, Carbon::now()->month) as $month) {
            foreach ($categories as $category) {
                DB::table('pengeluaran')->insert([
                    'pengeluaran_kategori_id' => array_rand($categories) + 1,
                    'keperluan' => rand(500000, 5000000),
                    'nominal' => rand(500000, 5000000),
                    'diajukan_pada' => Carbon::create(Carbon::now()->year, $month, rand(1, 28)),
                    'disetujui_pada' => Carbon::create(Carbon::now()->year, $month, rand(1, 28)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
