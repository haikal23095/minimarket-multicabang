<?php

namespace App\Console\Commands;

use App\Services\SyncTransactionService;
use Illuminate\Console\Command;

class SyncDataCommand extends Command
{
    protected $signature = 'sync:data';

    protected $description = 'Mengirim rekap transaksi cabang ke pusat';

    public function handle(SyncTransactionService $syncTransaction): int
    {
        $this->info('Memulai integrasi rekap transaksi...');

        $syncTransaction->sendRekapToPusat();
        $this->info('Integrasi rekap transaksi ke pusat sukses!');

        return self::SUCCESS;
    }
}
