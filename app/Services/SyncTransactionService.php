<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SyncTransactionService
{
    public function sendRekapToPusat($idCabang = 'CBG-MLG')
    {
        $today = now()->format('Y-m-d');

        $totalPenjualan = DB::connection('sqlsrv_malang')->table('Transaksi')
            ->whereDate('tanggal_transaksi', $today)
            ->sum('total_transaksi');

        $jumlahTransaksi = DB::connection('sqlsrv_malang')->table('Transaksi')
            ->whereDate('tanggal_transaksi', $today)
            ->count();

        if ($jumlahTransaksi > 0) {
            DB::connection('sqlsrv_pusat')->table('RekapTransaksi')->updateOrInsert(
                [
                    'id_cabang' => $idCabang,
                    'tanggal' => $today,
                ],
                [
                    'total_penjualan' => $totalPenjualan,
                    'jumlah_transaksi' => $jumlahTransaksi,
                    'updated_at' => now(),
                ]
            );
        }
    }
}
