<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PembayaranSeeder extends Seeder
{
    public function run()
    {
        $students = DB::table('siswa')->pluck('id')->toArray();
        $categories = DB::table('pembayaran_kategori')->pluck('id')->toArray();
        $kelas = DB::table('kelas')->pluck('id')->toArray();

        foreach (range(1, Carbon::now()->month) as $month) {
            foreach ($categories as $category) {
                DB::table('pembayaran')->insert([
                    'pembayaran_kategori_id' => array_rand($categories) + 1,
                    'kelas_id' => array_rand($kelas) + 1,
                    'siswa_id'=> array_rand($students) + 1,
                    'nominal' => rand(500000, 5000000),
                    'status' => 1,
                    'created_at' => Carbon::create(Carbon::now()->year, $month, rand(1, 28)),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

