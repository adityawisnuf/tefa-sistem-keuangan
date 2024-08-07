<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AsetSekolah>
 */
class AsetSekolahFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => $this->faker->streetName(),
            'kondisi' => $this->faker->randomElement(['Lecet 99%', 'Mulus', 'Batangan', 'Baru unbox', 'Fatal']),
            'penggunaan' => $this->faker->randomDigitNotNull() . " " . $this->faker->randomElement(['Tahun', 'Bulan', 'Minggu', 'Hari', 'Jam']),
            'tipe' => $this->faker->randomElement(['Tahunan', 'Bulanan', 'Mingguan']),

        ];
    }
}
