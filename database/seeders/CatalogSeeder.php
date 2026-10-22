<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $node = env('APP_NODE', 'malang');
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

        if (! in_array($node, ['pusat', 'malang', 'surabaya'], true)) {
            throw new \InvalidArgumentException("APP_NODE tidak dikenal: {$node}");
        }

        $branchIds = $node === 'pusat'
            ? array_keys($branches)
            : [$nodeToBranch[$node]];

        foreach ($branchIds as $branchId) {
            Cabang::updateOrCreate(
                ['id_cabang' => $branchId],
                $branches[$branchId],
            );
        }

        $categories = [
            'KTG001' => 'Minuman',
            'KTG002' => 'Makanan',
        ];

        foreach ($categories as $id => $name) {
            Kategori::updateOrCreate(
                ['id_kategori' => $id],
                ['nama_kategori' => $name],
            );
        }

        $products = [
            ['id_produk' => 'PRD001', 'id_kategori' => 'KTG001', 'nama_produk' => 'Air Mineral 600 ml', 'harga_jual' => 4000, 'jumlah_stok' => 100],
            ['id_produk' => 'PRD002', 'id_kategori' => 'KTG002', 'nama_produk' => 'Mi Instan', 'harga_jual' => 3500, 'jumlah_stok' => 100],
            ['id_produk' => 'PRD003', 'id_kategori' => 'KTG002', 'nama_produk' => 'Beras 5 kg', 'harga_jual' => 75000, 'jumlah_stok' => 25],
        ];

        foreach ($products as $product) {
            Produk::updateOrCreate(
                ['id_produk' => $product['id_produk']],
                [
                    'id_kategori' => $product['id_kategori'],
                    'nama_produk' => $product['nama_produk'],
                    'harga_jual' => $product['harga_jual'],
                ],
            );

            if ($node !== 'pusat') {
                Stok::firstOrCreate(
                    ['id_cabang' => $branchIds[0], 'id_produk' => $product['id_produk']],
                    ['jumlah_stok' => $product['jumlah_stok']],
                );
            }
        }
    }
}
