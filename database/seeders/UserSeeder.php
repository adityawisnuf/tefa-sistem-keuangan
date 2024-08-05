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
        $user = [
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'role' => 'Admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Kepala Sekolah',
                'email' => 'kepsek@gmail.com',
                'role' => 'KepalaSekolah',
                'password' => password_hash('kepsek123', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Bendahara',
                'email' => 'bendahara@gmail.com',
                'role' => 'Bendahara',
                'password' => password_hash('bendahara123', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Orang Tua',
                'email' => 'ortu@gmail.com',
                'role' => 'OrangTua',
                'password' => password_hash('ortu123', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Siswa',
                'email' => 'siswa@gmail.com',
                'role' => 'Siswa',
                'password' => password_hash('siswa123', PASSWORD_DEFAULT),
            ],
        ];

        foreach ($user as $data) {
            User::create($data);
        }

        $this->call(SekolahSeeder::class);
    }
}
