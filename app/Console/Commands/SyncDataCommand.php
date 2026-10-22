<?php

namespace App\Console\Commands;

use App\Services\SyncProductService;
use App\Services\SyncTransactionService;
use Illuminate\Console\Command;

class SyncDataCommand extends Command
{
    protected $signature = 'sync:data';

    protected $description = 'Melakukan sinkronisasi replikasi produk dan rekap transaksi';

    public function handle(SyncProductService $syncProduct, SyncTransactionService $syncTransaction)
    {
        $this->info('Memulai sinkronisasi data...');

        try {
            $syncProduct->syncPusatToCabang();
            $this->info('Replikasi produk dari pusat ke cabang sukses!');

            $syncTransaction->sendRekapToPusat();
            $this->info('Integrasi rekap transaksi ke pusat sukses!');
        } catch (\Exception $e) {
            $this->error('Gagal sinkronisasi: '.$e->getMessage());
        }
    }
}
