<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnggaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data dasar yang akan diulang
        $data = [
            [
                'nama_anggaran' => 'Renovasi Lab Komputer',
                'nominal' => 50000000,
                'deskripsi' => 'Renovasi Lab Komputer untuk kegiatan pembelajaran siswa.',
                'status' => 1, // Diajukan
                'pengapprove' => 'John Doe',
                'pengapprove_jabatan' => 'Manager Keuangan',
                'catatan' => 'Menunggu persetujuan dari direksi.',
            ],
            [
                'nama_anggaran' => 'Pengadaan Komputer Baru',
                'nominal' => 15000000,
                'deskripsi' => 'Pengadaan 10 unit komputer baru.',
                'status' => 2, // Diapprove
                'pengapprove' => 'Jane Smith',
                'pengapprove_jabatan' => 'Direktur IT',
                'catatan' => 'Sudah diapprove, menunggu proses pembelian.',
            ],
            [
                'nama_anggaran' => 'Pembelian Fasilitas Olahraga',
                'nominal' => 10000000,
                'deskripsi' => 'Pembelian Fasilitas Olahraga untuk mendukung kegiatan olahraga siswa.',
                'status' => 3, // Terealisasikan
                'pengapprove' => 'Marry Jane',
                'pengapprove_jabatan' => 'Direktur Olahraga',
                'catatan' => 'Sudah terealisasikan.',
            ],
            [
                'nama_anggaran' => 'Pembangunan Kolam Renang',
                'nominal' => 150000000,
                'deskripsi' => 'Pembangunan Kolam Renang untuk mendukung kegiatan olahraga renang.',
                'status' => 4, // Gagal
                'pengapprove' => 'Marry Jane',
                'pengapprove_jabatan' => 'Direktur Olahraga',
                'catatan' => 'Gagal, kondisi keuangan sedang buruk.',
            ]
        ];

        // Loop 5 kali untuk setiap data di atas, selama 5 tahun ke depan
        for ($i = 0; $i < 5; $i++) {
            foreach ($data as $item) {
                $tahunMendatang = Carbon::now()->addYears($i); // Menambahkan tahun
                $randomTanggalPengajuan = Carbon::create($tahunMendatang->year, rand(1, 12), rand(1, 28)); // Random bulan dan tanggal
                $randomTanggalTerealisasi = $randomTanggalPengajuan->copy()->addMonths(rand(1, 3)); // Target terealisasi dalam 1-3 bulan

                DB::table('anggaran')->insert([
                    'nama_anggaran' => $item['nama_anggaran'],
                    'nominal' => $item['nominal'],
                    'deskripsi' => $item['deskripsi'],
                    'tanggal_pengajuan' => $randomTanggalPengajuan,
                    'target_terealisasikan' => $randomTanggalTerealisasi,
                    'status' => $item['status'],
                    'pengapprove' => $item['pengapprove'],
                    'pengapprove_jabatan' => $item['pengapprove_jabatan'],
                    'catatan' => $item['catatan'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
