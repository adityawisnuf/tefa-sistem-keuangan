<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class KantinProdukKategoriFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\KantinProdukKategori::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'nama_kategori' => $this->faker->word,
            'deskripsi' => $this->faker->sentence,
        ];
    }
}
