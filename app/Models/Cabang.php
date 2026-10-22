<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    use HasFactory;

    protected $table = 'Cabang';

    protected $primaryKey = 'id_cabang';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_cabang',
        'nama_cabang',
        'alamat',
    ];
}
