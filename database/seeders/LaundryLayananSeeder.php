<?php

namespace Database\Seeders;

use App\Models\LaundryLayanan;
use Illuminate\Database\Seeder;

class LaundryLayananSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        for ($usahaId = 6; $usahaId <= 10; $usahaId++) {
            LaundryLayanan::factory(20)->create([
                'usaha_id' => $usahaId,
            ]);
        }
    }
}