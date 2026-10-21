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
        $pegawaiKasir = Pegawai::firstOrCreate(
            ['no_hp' => '081234567890'],
            [
                'nama_pegawai' => 'Budi Kasir Malang',
                'jabatan' => 'Kasir',
                'alamat' => 'Jl. Soekarno Hatta No. 12, Malang',
            ],
        );

        $pegawaiAdmin = Pegawai::firstOrCreate(
            ['no_hp' => '081298765432'],
            [
                'nama_pegawai' => 'Siti Admin Malang',
                'jabatan' => 'Kepala Cabang',
                'alamat' => 'Jl. Veteran No. 5, Malang',
            ],
        );

        User::updateOrCreate(
            ['username' => 'kasir_mlg'],
            [
                'pegawai_id' => $pegawaiKasir->id_pegawai,
                'password' => Hash::make('password123'),
                'role' => 'kasir',
            ],
        );

        User::updateOrCreate(
            ['username' => 'admin_mlg'],
            [
                'pegawai_id' => $pegawaiAdmin->id_pegawai,
                'password' => Hash::make('password123'),
                'role' => 'admin_cabang',
            ],
        );
    }
}
