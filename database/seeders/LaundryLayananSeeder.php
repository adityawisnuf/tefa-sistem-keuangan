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
        LaundryLayanan::factory(15)->create();
    }
}