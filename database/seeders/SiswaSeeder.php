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

        for ($i = 0; $i < 10; $i++) {
            Siswa::create([
                'user_id' =>1, // Replace with actual user IDs
                'nama_depan' => $faker->firstName(),
                'nama_belakang' => $faker->lastName(),
                'alamat' => $faker->address(),
                'tempat_lahir' => $faker->city(),
                'tanggal_lahir' => $faker->dateTimeBetween('-18 years')->format('Y-m-d'),
                'telepon' => $faker->phoneNumber(),
                'kelas_id' =>1,
                'orangtua_id' => 1,
                'village_id' => rand(1, 10),
            ]);
        }
    }
}
