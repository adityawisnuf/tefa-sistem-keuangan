<?php

namespace Database\Seeders;

use App\Models\PembayaranSiswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PembayaranSiswaSeeder extends Seeder
{
    protected $table = 'pembayaran_siswa';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $siswaIds = [1, 2];
        foreach ($siswaIds as $index => $siswaId) {
            PembayaranSiswa::create([
                'siswa_id' => 1,
                'pembayaran_id' => 1,
                'nominal' => 500000,
                'status' => $index % 2 === 0 ? 1 : 0,  // Status 1 untuk ganjil, 0 untuk genap
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            PembayaranSiswa::create([
                'siswa_id' => 1,
                'pembayaran_id' => 2,
                'nominal' => 5000000,
                'status' => $index % 2 === 0 ? 0 : 1,  // Status 0 untuk ganjil, 1 untuk genap
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            PembayaranSiswa::create([
                'siswa_id' => 2,
                'pembayaran_id' => 1,
                'nominal' => 500000,
                'status' => $index % 2 === 0 ? 1 : 0,  // Status 1 untuk ganjil, 0 untuk genap
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            PembayaranSiswa::create([
                'siswa_id' => 2,
                'pembayaran_id' => 2,
                'nominal' => 5000000,
                'status' => $index % 2 === 0 ? 0 : 1,  // Status 0 untuk ganjil, 1 untuk genap
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
