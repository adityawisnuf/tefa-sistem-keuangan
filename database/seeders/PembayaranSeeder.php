<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PembayaranSeeder extends Seeder
{
    public function run()
    {
        // $students = DB::table('siswa')->pluck('id')->toArray();
        // $categories = DB::table('pembayaran_kategori')->pluck('id')->toArray();
        // $kelas = DB::table('kelas')->pluck('id')->toArray();

        foreach (range(1, Carbon::now()->month) as $month) {
            DB::table('pembayaran')->insert([
                'pembayaran_kategori_id' => 3,
                'kelas_id' => null,
                'siswa_id'=> null,
                'nominal' => 200000,
                'status' => 1,
                'created_at' => Carbon::create(Carbon::now()->year, $month, rand(1, 28)),
                'updated_at' => now(),
            ]);
        }

        $pembayarans = [
            [
                'pembayaran_kategori_id' => 1,
                'kelas_id' => null,
                'siswa_id'=> null,
                'nominal' => 300000,
                'status' => 1,
                'created_at' => Carbon::create(Carbon::now()->year, $month, rand(1, 28)),
                'updated_at' => now(),
            ],
            [
                'pembayaran_kategori_id' => 2,
                'kelas_id' => null,
                'siswa_id'=> null,
                'nominal' => 215000,
                'status' => 1,
                'created_at' => Carbon::create(Carbon::now()->year, $month, rand(1, 28)),
                'updated_at' => now(),
            ]
        ];

        foreach($pembayarans as $p){
            DB::table('pembayaran')->insert($p);
        }
    }
}

