<?php

namespace Database\Seeders;

use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        $siswa = [
            [
                'user_id' => 6, // Replace with actual user IDs
                'nama_depan' => 'Ahmad',
                'nama_belakang' => 'Fauzi',
                'alamat' => $faker->address(),
                'tempat_lahir' => $faker->city(),
                'tanggal_lahir' => $faker->dateTimeBetween('-18 years')->format('Y-m-d'),
                'telepon' => $faker->phoneNumber(),
                'kelas_id' => 1,
                'orangtua_id' => 1,
                'village_id' => rand(1, 10),
            ],
            [
                'user_id' => 7, // Replace with actual user IDs
                'nama_depan' => 'Dewi',
                'nama_belakang' => 'Lestari',
                'alamat' => $faker->address(),
                'tempat_lahir' => $faker->city(),
                'tanggal_lahir' => $faker->dateTimeBetween('-18 years')->format('Y-m-d'),
                'telepon' => $faker->phoneNumber(),
                'kelas_id' => 1,
                'orangtua_id' => 1,
                'village_id' => rand(1, 10),
            ],
        ];
        foreach ($siswa as $data) {
            Siswa::create($data);
        }
    }
}
