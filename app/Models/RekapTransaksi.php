<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapTransaksi extends Model
{
    protected $table = 'RekapTransaksi';

    protected $primaryKey = 'id_rekap';

    protected $fillable = [
        'id_cabang',
        'tanggal',
        'total_penjualan',
        'jumlah_transaksi',
    ];
}
