<?php

namespace Database\Seeders;

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
        \App\Models\LaundryLayanan::factory(10)->create();
    }
}
