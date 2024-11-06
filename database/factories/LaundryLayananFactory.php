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

        $tipe = $this->faker->randomElement(['satuan', 'kiloan']);

        $satuan = $tipe === 'satuan' ? 'pcs' : 'kg';

        return [
            'usaha_id' => 6, 
            'nama_layanan' => $this->faker->words(2, true), 
            'foto_layanan' => $this->faker->imageUrl(640, 480, 'laundry', true), 
            'deskripsi' => $this->faker->sentence(), 
            'harga' => $this->faker->numberBetween(5000, 50000), 
            'tipe' => $tipe,
            'satuan' => $satuan, 
            'status' => 'aktif',
        ];
    }
}