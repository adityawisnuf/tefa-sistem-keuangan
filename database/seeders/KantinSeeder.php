<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KantinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users_kantin = [
            [
                'name' => 'Warung Pak Agus',
                'email' => 'kantin.warungpakagus@example.com',
                'password' => 'warungpakagus123',
                'role' => 'Kantin',
            ],
            [
                'name' => 'Warkop Bu Badriah',
                'email' => 'kantin.warkopbubadriah@example.com',
                'password' => 'pisangbubadriah123',
                'role' => 'Kantin',
            ],
            [
                'name' => 'Warteg Ibu Siti M',
                'email' => 'kantin.wartegibusitim@example.com',
                'password' => 'wartegibusitim123',
                'role' => 'Kantin',
            ]
        ];
        
        foreach($users_kantin as $idx => $uk){
            $user = \App\Models\User::create($uk);

            $kantin = \App\Models\Usaha::create([
                'user_id' => $user->id,
                'nama_usaha' => $user->name,
                'alamat' => 'Cianjur',
                'no_telepon' => '088888888888',
                'no_rekening' => '4000000000000044',
                'saldo' => 0,
                'status_buka' => 'buka'
            ]);
        }
    }
}
