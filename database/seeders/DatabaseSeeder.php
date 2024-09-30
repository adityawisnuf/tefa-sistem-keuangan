<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::beginTransaction();
        $this->call([
            \Database\Seeders\IndoRegionSeeder::class,
        ]);

        try {
            \App\Models\User::create([
                'name' => 'Administrator',
                'email' => 'admin@gmail.com',
                'role' => 'Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('rahasia'),
            ]);

            \App\Models\User::create([
                'name' => 'Kepala Sekolah User',
                'email' => 'kepalasekolah@gmail.com',
                'role' => 'Kepala Sekolah',
                'password' => \Illuminate\Support\Facades\Hash::make('rahasia'),
            ]);

            \App\Models\User::create([
                'name' => 'Bendahara User',
                'email' => 'bendahara@gmail.com',
                'role' => 'Bendahara',
                'password' => \Illuminate\Support\Facades\Hash::make('rahasia'),
            ]);

            $SiswaUser = \App\Models\User::create([
                'name' => 'Muhammad Azfa',
                'email' => 'siswa@gmail.com',
                'role' => 'Siswa',
                'password' => \Illuminate\Support\Facades\Hash::make('rahasia'),
            ]);

            $OrangTuaUser = \App\Models\User::create([
                'name' => 'Orang Tua User',
                'email' => 'orangtua@gmail.com',
                'role' => 'Orang Tua',
                'password' => \Illuminate\Support\Facades\Hash::make('rahasia'),
            ]);

            \App\Models\User::create([
                'name' => 'Kantin User',
                'email' => 'kantin@gmail.com',
                'role' => 'Kantin',
                'password' => \Illuminate\Support\Facades\Hash::make('rahasia'),
            ]);

            \App\Models\User::create([
                'name' => 'Laundry User',
                'email' => 'laundry@gmail.com',
                'role' => 'Laundry',
                'password' => \Illuminate\Support\Facades\Hash::make('rahasia'),
            ]);

            \App\Models\Orangtua::insert([
                'id' => $OrangTuaUser->id,
                'nama' => 'Ibu Siswa',
                'user_id' => $OrangTuaUser->id,
            ]);
            $Sekolah = \App\Models\Sekolah::create([
                'nama' => 'SMKN 2 Sumedang',
                'alamat' => 'sumedang',
                'telepon' => '085156105763',
            ]);
            $Kelas = \App\Models\Kelas::create([
                'sekolah_id' => $Sekolah->id,
                'jurusan' => 'PPLG',
                'kelas' => 'X PPLG 1',
            ]);
            \App\Models\Siswa::create([
                'user_id' => $SiswaUser->id,
                'nama_depan' => 'Azfa',
                'nama_belakang' => 'Salman',
                'alamat' => 'Sumedang',
                'tempat_lahir' => 'Purwakarta',
                'village_id' => '1',
                'tanggal_lahir' => '2024-08-29',
                'orangtua_id' => $OrangTuaUser->id,
                'kelas_id' => $Kelas->id,
            ]);
            $PembayaranKategori = \App\Models\PembayaranKategori::create([
                'nama' => 'SPP 2024/2025',
                'jenis_pembayaran' => 1,
                'tanggal_pembayaran' => '2024-08-29',
                'status' => 1,
            ]);
            for ($i = 1; $i <= 12; $i++) {
                \App\Models\Pembayaran::create([
                    'pembayaran_kategori_id' => 2,
                    'kelas_id' => 1,
                    'nominal' => 100000,
                    'pembayaran_ke' => $i,
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        } finally {
            DB::commit();
        }
    }
}
