<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'Pegawai';

    protected $primaryKey = 'id_pegawai';

    protected $fillable = [
        'nama_pegawai',
        'jabatan',
        'no_hp',
        'alamat',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->hasOne(User::class, 'pegawai_id', 'id_pegawai');
    }

    // Relasi ke Transaksi
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_pegawai', 'id_pegawai');
    }
}
