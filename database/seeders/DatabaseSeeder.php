<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'admin123',
            'role' => 'Admin',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'siswa',
            'email' => 'siswa@example.com',
            'password' => 'siswa123',
            'role' => 'Siswa',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'kantin',
            'email' => 'kantin@example.com',
            'password' => 'kantin123',
            'role' => 'Kantin',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'laundry',
            'email' => 'laundry@example.com',
            'password' => 'laundry123',
            'role' => 'Laundry',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'bendahara',
            'email' => 'bendahara@example.com',
            'password' => 'bendahara123',
            'role' => 'Bendahara',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'orangtua',
            'email' => 'orangtua@example.com',
            'password' => 'orangtua123',
            'role' => 'Orangtua',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'kepsek',
            'email' => 'kepsek@example.com',
            'password' => 'kepsek123',
            'role' => 'Kepsek',
        ]);

        \App\Models\Orangtua::create([
            'user_id' => '6',
            'nama' => 'Ega',
        ]);

        \App\Models\Sekolah::create([
            'nama' => 'SMKN 1 Cianjur',
            'alamat' => 'Cianjur',
            'telepon' => '088888888888',
        ]);

        \App\Models\Kelas::create([
            'sekolah_id' => '1',
            'jurusan' => 'PPLG',
            'kelas' => 'XI'
        ]);

        \App\Models\Siswa::create([
            'user_id' => '2',
            'nama_depan' => 'Ega',
            'nama_belakang' => 'Masardi',
            'village_id' => '1',
            'kelas_id' => '1',
            'alamat' => 'Cibaduyut',
            'tempat_lahir' => 'Cibaduyut',
            'tanggal_lahir' => now(),
            'telepon' => '088888888888',
        ]);

        \App\Models\SiswaWallet::create([
            'siswa_id' => '1',
            'nominal' => 0
        ]);

        \App\Models\Laundry::create([
            'user_id' => '4',
            'nama_laundry' => 'Laundry Ega',
            'alamat' => 'Cimahi',
            'no_telepon' => '088888888888',
            'no_rekening' => '4000000000000044',
            'saldo' => 0,
            'status_buka' => 'tutup'
        ]);

        \App\Models\Kantin::create([
            'user_id' => '4',
            'nama_kantin' => 'Kantin Ega',
            'alamat' => 'Cimahi',
            'no_telepon' => '088888888888',
            'no_rekening' => '4000000000000044',
            'saldo' => 0,
            'status_buka' => 'tutup'
        ]);
    }
}
