<?php

namespace Database\Factories;

use App\Models\DetailTransaksi;
use App\Models\Produk;
use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetailTransaksi>
 */
class DetailTransaksiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_transaksi' => Transaksi::factory(),
            'id_produk' => Produk::factory(),
            'qty' => fake()->numberBetween(1, 20),
            'harga' => fake()->randomFloat(2, 1000, 500000),
            'subtotal' => static fn (array $attributes): float => (int) $attributes['qty'] * (float) $attributes['harga'],
        ];
    }
}
