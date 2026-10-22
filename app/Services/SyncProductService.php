<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SyncProductService
{
    public function syncPusatToCabang()
    {
        // 1. Ambil data produk dari db_pusat
        $produkPusat = DB::connection('sqlsrv_pusat')->table('Produk')->get();

        foreach ($produkPusat as $item) {
            // 2. Sync ke Cabang Malang
            DB::connection('sqlsrv_malang')->table('Produk')->updateOrInsert(
                ['id_produk' => $item->id_produk],
                [
                    'id_kategori' => $item->id_kategori ?? null,
                    'nama_produk' => $item->nama_produk,
                    'harga_jual' => $item->harga_jual,
                ]
            );
        }
    }
}
