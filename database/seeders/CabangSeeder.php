<?php

namespace Database\Seeders;

use App\Models\Cabang;
use Illuminate\Database\Seeder;

class CabangSeeder extends Seeder
{
    public function run(): void
    {
        $node = env('APP_NODE', 'malang');

        $branches = [
            'malang' => [
                'id_cabang' => 'MLG',
                'nama_cabang' => 'Minimarket Malang',
                'alamat' => 'Jl. Soekarno Hatta, Malang',
            ],
            'surabaya' => [
                'id_cabang' => 'SBY',
                'nama_cabang' => 'Minimarket Surabaya',
                'alamat' => 'Jl. Tunjungan, Surabaya',
            ],
        ];

        if ($node === 'pusat') {
            foreach ($branches as $branch) {
                Cabang::updateOrCreate(['id_cabang' => $branch['id_cabang']], $branch);
            }
        } else {
            if (isset($branches[$node])) {
                $branch = $branches[$node];
                Cabang::updateOrCreate(['id_cabang' => $branch['id_cabang']], $branch);
            }
        }
    }
}
