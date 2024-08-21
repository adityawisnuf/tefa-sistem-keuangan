<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PembayaranSiswaSeeder extends Seeder
{
    public function run()
    {
        $students = DB::table('siswa')->pluck('id')->toArray();
        $payments = DB::table('pembayaran')->get();

        foreach ($payments as $payment) {
            foreach ($students as $student) {
                DB::table('pembayaran_siswa')->insert([
                    'siswa_id' => $student,
                    'pembayaran_id' => $payment->id,
                    'nominal' => $payment->nominal,
                    'status' => rand(1, 0),
                    'created_at' => $payment->created_at,
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
