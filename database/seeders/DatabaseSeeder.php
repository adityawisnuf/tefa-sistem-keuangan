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
            'name' => 'kantin2',
            'email' => 'kantin2@example.com',
            'password' => 'kantin123',
            'role' => 'Kantin',
        ]);
        \App\Models\User::factory()->create([
            'name' => 'kantin3',
            'email' => 'kantin3@example.com',
            'password' => 'kantin123',
            'role' => 'Kantin',
        ]);
        \App\Models\User::factory()->create([
            'name' => 'kantin4',
            'email' => 'kantin4@example.com',
            'password' => 'kantin123',
            'role' => 'Kantin',
        ]);
        \App\Models\User::factory()->create([
            'name' => 'kantin5',
            'email' => 'kantin5@example.com',
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
            'name' => 'laundry2',
            'email' => 'laundry2@example.com',
            'password' => 'laundry123',
            'role' => 'Laundry',
        ]);
        \App\Models\User::factory()->create([
            'name' => 'laundry3',
            'email' => 'laundry3@example.com',
            'password' => 'laundry123',
            'role' => 'Laundry',
        ]);
        \App\Models\User::factory()->create([
            'name' => 'laundry4',
            'email' => 'laundry4@example.com',
            'password' => 'laundry123',
            'role' => 'Laundry',
        ]);
        \App\Models\User::factory()->create([
            'name' => 'laundry5',
            'email' => 'laundry5@example.com',
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
            'role' => 'OrangTua',
        ]);
        
        \App\Models\User::factory()->create([
            'name' => 'siswa2',
            'email' => 'siswa2@example.com',
            'password' => 'siswa2123',
            'role' => 'Siswa',
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
            'user_id' => '8',
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
            'siswa_id' => '2',
            'nominal' => 100000
        ]);

        \App\Models\User::factory()->create([
            'name' => 'kepsek',
            'email' => 'kepsek@example.com',
            'password' => 'kepsek123',
            'role' => 'KepalaSekolah',
        ]);

        \App\Models\Orangtua::create([
            'user_id' => '6',
            'nama' => 'Ega',
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
            'nominal' => 100000
        ]);

        \App\Models\Usaha::create([
            'user_id' => '3',
            'nama_usaha' => 'Kantin Ega',
            'alamat' => 'Cimahi',
            'no_telepon' => '088888888888',
            'no_rekening' => '4000000000000044',
            'saldo' => 0,
            'status_buka' => 'tutup'
        ]);
        \App\Models\Usaha::create([
            'user_id' => '4',
            'nama_usaha' => 'Kantin Eka',
            'alamat' => 'Bandung',
            'no_telepon' => '088888888888',
            'no_rekening' => '4000000000000044',
            'saldo' => 0,
            'status_buka' => 'tutup'
        ]);
        \App\Models\Usaha::create([
            'user_id' => '5',
            'nama_usaha' => 'Kantin Satria',
            'alamat' => 'Cianjur',
            'no_telepon' => '088888888888',
            'no_rekening' => '4000000000000044',
            'saldo' => 0,
            'status_buka' => 'tutup'
        ]);
        \App\Models\Usaha::create([
            'user_id' => '6',
            'nama_usaha' => 'Kantin Dani',
            'alamat' => 'Jakarta',
            'no_telepon' => '088888888888',
            'no_rekening' => '4000000000000044',
            'saldo' => 0,
            'status_buka' => 'tutup'
        ]);
        \App\Models\Usaha::create([
            'user_id' => '7',
            'nama_usaha' => 'Kantin Fairuz',
            'alamat' => 'Cipanas',
            'no_telepon' => '088888888888',
            'no_rekening' => '4000000000000044',
            'saldo' => 0,
            'status_buka' => 'tutup'
        ]);

        \App\Models\Usaha::create([
            'user_id' => '8',
            'nama_usaha' => 'Laundry Ega',
            'alamat' => 'Cimahi',
            'no_telepon' => '088888888888',
            'no_rekening' => '4000000000000044',
            'saldo' => 0,
            'status_buka' => 'tutup'
        ]);
        \App\Models\Usaha::create([
            'user_id' => '9',
            'nama_usaha' => 'Laundry Eka',
            'alamat' => 'Cimahi',
            'no_telepon' => '088888888888',
            'no_rekening' => '4000000000000044',
            'saldo' => 0,
            'status_buka' => 'tutup'
        ]);
        \App\Models\Usaha::create([
            'user_id' => '10',
            'nama_usaha' => 'Laundry Satria',
            'alamat' => 'Cimahi',
            'no_telepon' => '088888888888',
            'no_rekening' => '4000000000000044',
            'saldo' => 0,
            'status_buka' => 'tutup'
        ]);
        \App\Models\Usaha::create([
            'user_id' => '11',
            'nama_usaha' => 'Laundry Dani',
            'alamat' => 'Cimahi',
            'no_telepon' => '088888888888',
            'no_rekening' => '4000000000000044',
            'saldo' => 0,
            'status_buka' => 'tutup'
        ]);
        \App\Models\Usaha::create([
            'user_id' => '12',
            'nama_usaha' => 'Laundry Fairuz',
            'alamat' => 'Cimahi',
            'no_telepon' => '088888888888',
            'no_rekening' => '4000000000000044',
            'saldo' => 0,
            'status_buka' => 'tutup'
        ]);

        \App\Models\KantinProdukKategori::create([
            'nama_kategori' => 'makanan',
            'deskripsi' => 'makanan',
        ]);

        \App\Models\KantinProdukKategori::create([
            'nama_kategori' => 'minuman',
            'deskripsi' => 'minuman',
        ]);

        $this->call([
            KantinProdukSeeder::class,
            LaundryLayananSeeder::class
        ]);

    }
}