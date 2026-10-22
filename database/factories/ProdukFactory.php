<?php

namespace Database\Factories;

use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Produk>
 */
class ProdukFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_produk' => 'PRD'.fake()->unique()->numerify('############'),
            'id_kategori' => Kategori::factory(),
            'nama_produk' => fake()->words(3, true),
            'harga_jual' => fake()->randomFloat(2, 1000, 500000),
        ];
    }
}
