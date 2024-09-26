<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'Admin'
        ]);
        User::create([
            'name' => 'Kepala Sekolah',
            'email' => 'kepalasekolah@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'KepalaSekolah'
        ]);
        User::create([
            'name' => 'Bendahara',
            'email' => 'bendahara@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'Bendahara'
        ]);
        User::create([
            'name' => 'Orang Tua',
            'email' => 'orangtua@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'OrangTua'
        ]);
        User::create([
            'name' => 'Siswa',
            'email' => 'siswa@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'Siswa'
        ]);
    }
}
