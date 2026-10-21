<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $connection = 'sqlsrv_malang';
    protected $table = 'Produk';
    protected $primaryKey = 'id_produk';

    public $incrementing = false; // Beritahu Eloquent bahwa PK bukan auto-increment integer
    protected $keyType = 'string';

    protected $fillable = [
        'id_produk',
        'id_kategori',
        'nama_produk',
        'harga_jual',
    ];

    // Relasi ke Kategori
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }
    // Relasi ke Stok
    public function stok()
    {
        return $this->hasOne(Stok::class, 'id_produk', 'id_produk');
    }

    // Relasi ke Detail Transaksi
    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_produk', 'id_produk');
    }
}