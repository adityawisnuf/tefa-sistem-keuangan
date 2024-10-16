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
                'name' => 'Budi Santoso',
                'email' => 'budi@gmail.com',
                'role' => 'OrangTua',
                'password' => password_hash('budi123', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti@gmail.com',
                'role' => 'OrangTua',
                'password' => password_hash('siti123', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@gmail.com',
                'role' => 'Siswa',
                'password' => password_hash('ahmad123', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@gmail.com',
                'role' => 'Siswa',
                'password' => password_hash('dewi123', PASSWORD_DEFAULT),
            ],
        ];

        foreach ($user as $data) {
            User::create($data);
        }

        User::create([
            'name' => 'Kepala Sekolah',
            'email' => 'kepalasekolah@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'Kepala Sekolah'
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

        $this->call(SekolahSeeder::class);
    }
}
