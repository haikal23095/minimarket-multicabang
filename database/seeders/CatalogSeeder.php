<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Seed the application's branch and product catalog.
     */
    public function run(): void
    {
        Cabang::updateOrCreate(
            ['id_cabang' => 'MLG'],
            [
                'nama_cabang' => 'Minimarket Malang',
                'alamat' => 'Jl. Soekarno Hatta, Malang',
            ],
        );

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

            Stok::firstOrCreate(
                [
                    'id_cabang' => 'MLG',
                    'id_produk' => $product['id_produk'],
                ],
                ['jumlah_stok' => $product['jumlah_stok']],
            );
        }
    }
}
