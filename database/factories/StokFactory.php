<?php

namespace Database\Factories;

use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stok>
 */
class StokFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_cabang' => fake()->randomElement(['MLG', 'SBY']),
            'id_produk' => Produk::factory(),
            'jumlah_stok' => fake()->numberBetween(0, 300),
        ];
    }
}
