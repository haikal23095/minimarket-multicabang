<?php

namespace Database\Factories;

use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pegawai>
 */
class PegawaiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_pegawai' => fake()->name(),
            'jabatan' => fake()->randomElement(['Kasir', 'Kepala Cabang', 'Staff']),
            'no_hp' => fake()->numerify('08##########'),
            'alamat' => fake()->address(),
        ];
    }
}
