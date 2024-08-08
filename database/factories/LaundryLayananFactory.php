<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LaundryLayananFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\LaundryLayanan::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'laundry_id' => 1, 
            'nama_layanan' => $this->faker->word,
            'foto_layanan' => $this->faker->imageUrl(),
            'deskripsi' => $this->faker->sentence,
            'harga_per_kilo' => $this->faker->numberBetween(10000, 50000),
            'status' => $this->faker->randomElement(['aktif', 'tidak_aktif']),
        ];
    }
}
