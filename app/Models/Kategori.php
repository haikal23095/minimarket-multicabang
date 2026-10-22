<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'Kategori';

    protected $primaryKey = 'id_kategori';

    // Karena id_kategori bernilai string (contoh: KTG001)
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_kategori',
        'nama_kategori',
    ];

    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_kategori', 'id_kategori');
    }
}
