<?php

namespace Database\Factories;

use App\Models\Pegawai;
use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaksi>
 */
class TransaksiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_transaksi' => 'TRX'.fake()->unique()->numerify('############'),
            'no_faktur' => 'INV-'.fake()->unique()->numerify('############'),
            'id_cabang' => fake()->randomElement(['MLG', 'SBY']),
            'id_pegawai' => Pegawai::factory(),
            'tanggal_transaksi' => fake()->dateTimeThisYear(),
            'total_transaksi' => fake()->randomFloat(2, 1000, 500000),
            'bayar' => static fn (array $attributes): float => (float) $attributes['total_transaksi']
                + fake()->randomFloat(2, 0, 50000),
        ];
    }
}
