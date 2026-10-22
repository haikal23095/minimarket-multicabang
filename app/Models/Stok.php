<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stok extends Model
{
    use HasFactory;

    protected $table = 'Stok';

    protected $primaryKey = ['id_cabang', 'id_produk'];

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id_cabang',
        'id_produk',
        'jumlah_stok',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    // Custom method untuk update key ganda pada Eloquent
    protected function setKeysForSaveQuery($query)
    {
        $query->where('id_cabang', $this->getAttribute('id_cabang'))
            ->where('id_produk', $this->getAttribute('id_produk'));

        return $query;
    }
}
