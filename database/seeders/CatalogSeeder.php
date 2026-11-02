<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Cara membaca opsi --database yang benar di Laravel Seeder
        $connectionName = $this->command ? ($this->command->option('database') ?: config('database.default')) : config('database.default');
        
        $node = env('APP_NODE', 'surabaya');
        $nodeToBranch = [
            'malang' => 'MLG',
            'surabaya' => 'SBY',
        ];

        $branches = [
            'MLG' => [
                'nama_cabang' => 'Minimarket Malang',
                'alamat' => 'Jl. Soekarno Hatta, Malang',
            ],
            'SBY' => [
                'nama_cabang' => 'Minimarket Surabaya',
                'alamat' => 'Jl. Tunjungan, Surabaya',
            ],
        ];

        $branchIds = $node === 'pusat'
            ? array_keys($branches)
            : [$nodeToBranch[$node] ?? 'SBY'];

        // 1. Seed Cabang
        foreach ($branchIds as $branchId) {
            DB::connection($connectionName)->table('Cabang')->updateOrInsert(
                ['id_cabang' => $branchId],
                $branches[$branchId]
            );
        }

        // 2. Seed Kategori
        $categories = [
            'KTG001' => 'Minuman',
            'KTG002' => 'Makanan',
        ];

        foreach ($categories as $id => $name) {
            DB::connection($connectionName)->table('Kategori')->updateOrInsert(
                ['id_kategori' => $id],
                ['nama_kategori' => $name]
            );
        }

        // 3. Seed Produk
        $products = [
            ['id_produk' => 'PRD001', 'id_kategori' => 'KTG001', 'nama_produk' => 'Air Mineral 600 ml', 'harga_jual' => 4000, 'jumlah_stok' => 100],
            ['id_produk' => 'PRD002', 'id_kategori' => 'KTG002', 'nama_produk' => 'Mi Instan', 'harga_jual' => 3500, 'jumlah_stok' => 100],
            ['id_produk' => 'PRD003', 'id_kategori' => 'KTG002', 'nama_produk' => 'Beras 5 kg', 'harga_jual' => 75000, 'jumlah_stok' => 25],
        ];

        foreach ($products as $product) {
            DB::connection($connectionName)->table('Produk')->updateOrInsert(
                ['id_produk' => $product['id_produk']],
                [
                    'id_kategori' => $product['id_kategori'],
                    'nama_produk' => $product['nama_produk'],
                    'harga_jual' => $product['harga_jual'],
                ]
            );

            // 4. Seed Stok
            if ($node !== 'pusat' || $connectionName === 'sqlsrv_surabaya') {
                DB::connection($connectionName)->table('Stok')->updateOrInsert(
                    ['id_cabang' => $branchIds[0], 'id_produk' => $product['id_produk']],
                    ['jumlah_stok' => $product['jumlah_stok']]
                );
            }
        }
    }
}