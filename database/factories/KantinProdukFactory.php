<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KantinProduk>
 */
class KantinProdukFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {

        return [
            'usaha_id' => 1,
            'kantin_produk_kategori_id' => $this->faker->numberBetween(1, 2), 
            'nama_produk' => $this->faker->words(2, true), 
            'foto_produk' => $this->faker->imageUrl(640, 480, 'makanan', true), 
            'deskripsi' => $this->faker->sentence(),
            'harga_pokok' => $this->faker->numberBetween(1000, 10000),
            'harga_jual' => $this->faker->numberBetween(11000, 20000),
            'stok' => $this->faker->numberBetween(10, 100),
            'status' => 'aktif',
        ];
    }
}