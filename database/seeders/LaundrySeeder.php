<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LaundrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::factory()->create([
            'name' => 'Laundry',
            'email' => 'laundry@example.com',
            'password' => 'laundry123',
            'role' => 'Laundry',
        ]);

        \App\Models\Usaha::create([
            'user_id' => $user->id,
            'nama_usaha' => $user->name,
            'alamat' => 'Cimahi',
            'no_telepon' => '088888888888',
            'no_rekening' => '4000000000000044',
            'saldo' => 0,
            'status_buka' => 'buka'
        ]);
    }
}
