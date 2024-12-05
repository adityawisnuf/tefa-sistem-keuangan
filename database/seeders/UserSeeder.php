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
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'role' => 'Admin',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Kepala Sekolah',
                'email' => 'kepsek@example.com',
                'role' => 'KepalaSekolah',
                'password' => password_hash('kepsek123', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Bendahara',
                'email' => 'bendahara@example.com',
                'role' => 'Bendahara',
                'password' => password_hash('bendahara123', PASSWORD_DEFAULT),
            ]
        ];

        foreach ($users as $data) {
            User::create($data);
        }
    }
}
