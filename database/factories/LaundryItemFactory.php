<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LaundryItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \App\Models\LaundryItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'laundry_id' => 1,
            'nama_item' => $this->faker->word,
            'foto_item' => $this->faker->imageUrl(),
            'deskripsi' => $this->faker->sentence,
            'harga' => $this->faker->numberBetween(10000, 100000),
            'status' => $this->faker->randomElement(['aktif', 'tidak_aktif']),
        ];
    }
}
