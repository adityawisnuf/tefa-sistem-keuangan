<?php

namespace Database\Seeders;

use App\Enums\Role;
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
            'role' => 'Kepala Sekolah'
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
            'role' => 'Orang Tua'
        ]);
        User::create([
            'name' => 'Siswa',
            'email' => 'siswa@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'Siswa'
        ]);

        User::factory(15)->create([
            'role' => Role::Siswa->value
        ]);

        User::factory(10)->create([
            'role' => Role::OrangTua->value
        ]);
    }
}
