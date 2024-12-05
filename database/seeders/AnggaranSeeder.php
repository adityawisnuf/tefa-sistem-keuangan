<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Anggaran;

class AnggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $anggarans = [
            [
                'nama_anggaran' => 'Anggaran Proyek Guru Tamu',
                'nominal' => 4000000,
                'nominal_diapprove' => 4000000,
                'deskripsi' => 'Anggaran untuk pembayaran guru tamu untuk jurusan PPLG',
                'tanggal_pengajuan' => Carbon::create(Carbon::now()->year, 12, 01),
                'target_terealisasikan' => Carbon::create(Carbon::now()->year, 12, 04),
                'status' => 3,
                'pengapprove' => 'Pak Budi',
                'pengapprove_jabatan' => 'Kepala Sekolah',
                'catatan' => null,
                'created_at' => now(),
            ],
            [
                'nama_anggaran' => 'Anggaran Proyek Tes Diagnosis Assessment',
                'nominal' => 10000000,
                'nominal_diapprove' => 10000000,
                'deskripsi' => 'Anggaran untuk penyelenggaraan tes diagnosis assessment serentak',
                'tanggal_pengajuan' => Carbon::create(Carbon::now()->year, 11, 25),
                'target_terealisasikan' => Carbon::create(Carbon::now()->year, 12, 10),
                'status' => 2,
                'pengapprove' => 'Pak Budi',
                'pengapprove_jabatan' => 'Kepala Sekolah',
                'catatan' => null,
                'created_at' => now(),
            ],
            [
                'nama_anggaran' => 'Anggaran Gathering Guru',
                'nominal' => 15000000,
                'nominal_diapprove' => 15000000,
                'deskripsi' => 'Anggaran untuk penyelenggaraan gathering guru ke Yogjakarta',
                'tanggal_pengajuan' => Carbon::create(Carbon::now()->year, 11, 10),
                'target_terealisasikan' => Carbon::create(Carbon::now()->year, 12, 20),
                'status' => 1,
                'pengapprove' => 'Pak Budi',
                'pengapprove_jabatan' => 'Kepala Sekolah',
                'catatan' => null,
                'created_at' => now(),
            ]
        ];

        foreach($anggarans as $a){
            Anggaran::create($a);
        }
    }
}
