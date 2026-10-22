<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserCabangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $node = env('APP_NODE', 'malang');

        if ($node === 'pusat') {
            User::updateOrCreate(
                ['username' => 'admin_pusat'],
                [
                    'pegawai_id' => null,
                    'password' => Hash::make('password123'),
                    'role' => 'admin_pusat',
                ],
            );

            return;
        }

        $branchUsers = match ($node) {
            'malang' => [
                'kasir' => [
                    'no_hp' => '081234567890',
                    'nama_pegawai' => 'Budi Kasir Malang',
                    'jabatan' => 'Kasir',
                    'alamat' => 'Jl. Soekarno Hatta No. 12, Malang',
                    'username' => 'kasir_mlg',
                    'role' => 'kasir',
                ],
                'admin' => [
                    'no_hp' => '081298765432',
                    'nama_pegawai' => 'Siti Admin Malang',
                    'jabatan' => 'Kepala Cabang',
                    'alamat' => 'Jl. Veteran No. 5, Malang',
                    'username' => 'admin_mlg',
                    'role' => 'admin_cabang',
                ],
            ],
            'surabaya' => [
                'kasir' => [
                    'no_hp' => '082234567890',
                    'nama_pegawai' => 'Budi Kasir Surabaya',
                    'jabatan' => 'Kasir',
                    'alamat' => 'Jl. Tunjungan No. 12, Surabaya',
                    'username' => 'kasir_sby',
                    'role' => 'kasir',
                ],
                'admin' => [
                    'no_hp' => '082298765432',
                    'nama_pegawai' => 'Siti Admin Surabaya',
                    'jabatan' => 'Kepala Cabang',
                    'alamat' => 'Jl. Pemuda No. 5, Surabaya',
                    'username' => 'admin_sby',
                    'role' => 'admin_cabang',
                ],
            ],
            default => throw new \InvalidArgumentException("APP_NODE tidak dikenal: {$node}"),
        };

        foreach ($branchUsers as $userData) {
            $pegawai = Pegawai::firstOrCreate(
                ['no_hp' => $userData['no_hp']],
                [
                    'nama_pegawai' => $userData['nama_pegawai'],
                    'jabatan' => $userData['jabatan'],
                    'alamat' => $userData['alamat'],
                ],
            );

            User::updateOrCreate(
                ['username' => $userData['username']],
                [
                    'pegawai_id' => $pegawai->id_pegawai,
                    'password' => Hash::make('password123'),
                    'role' => $userData['role'],
                ],
            );
        }
    }
}
