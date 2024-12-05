<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Siswa;
use App\Models\Orangtua;

class SiswaOrtuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        $users_ortu = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi.santoso@gmail.com',
                'role' => 'OrangTua',
                'password' => password_hash('budi123', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti.aminah@gmail.com',
                'role' => 'OrangTua',
                'password' => password_hash('siti123', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Daniel Situmorang',
                'email' => 'daniel.situmorang@gmail.com',
                'role' => 'OrangTua',
                'password' => password_hash('daniel123', PASSWORD_DEFAULT),
            ]
        ];

        $users_siswa = [
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad.fauzi@gmail.com',
                'role' => 'Siswa',
                'password' => password_hash('ahmad123', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi.lestari@gmail.com',
                'role' => 'Siswa',
                'password' => password_hash('dewi123', PASSWORD_DEFAULT),
            ],
            [
                'name' => 'Bimbim Zatnika',
                'email' => 'bimbim.zatnika@gmail.com',
                'role' => 'Siswa',
                'password' => password_hash('bimbim123', PASSWORD_DEFAULT),
            ]
        ];

        foreach ($users_ortu as $idx => $uo) {
            $user_ortu = User::create($uo);

            $ortu = Orangtua::create([
                'user_id' => $user_ortu->id,
                'nama' => $user_ortu->name,
            ]);

            $user_siswa = User::create($users_siswa[$idx]);

            $siswa = Siswa::create([
                'user_id' => $user_siswa->id,
                'nama_depan' => explode(" ", $user_siswa->name)[0],
                'nama_belakang' => explode(" ", $user_siswa->name)[1],
                'alamat' => 'Jalan Siliwangi Belakang',
                'village_id' => '3203200008',
                'tempat_lahir' => 'Cibaduyut',
                'tanggal_lahir' => $faker->dateTimeBetween('-18 years')->format('Y-m-d'),
                'telepon' => '081234567890',
                'kelas_id' => rand(1, 9),
                'orangtua_id' => $ortu->id,
                'created_at' => now(),
            ]);

            \App\Models\SiswaWallet::create([
                'siswa_id' => $siswa->id,
                'nominal' => 0
            ]);
        }
    }
}
