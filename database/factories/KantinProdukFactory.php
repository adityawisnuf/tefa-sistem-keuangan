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
            'kantin_id' => 1,
            'kantin_produk_kategori_id' => $this->faker->numberBetween(1, 10),
            'nama_produk' => $this->faker->word,
            'foto_produk' => $this->faker->imageUrl(),
            'deskripsi' => $this->faker->sentence,
            'harga' => $this->faker->numberBetween(1000, 100000),
            'stok' => $this->faker->numberBetween(1, 100),
            'status' => $this->faker->randomElement(['aktif', 'tidak_aktif']),
        ];
    }
}
