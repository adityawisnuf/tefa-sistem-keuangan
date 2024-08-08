<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kantin extends Model
{
    use HasFactory;

    protected $table = 'kantin';

    protected $fillable = [
        'user_id',
        'nama_kantin',
        'alamat',
        'no_telepon',
        'no_rekening',
        'saldo',
        'status_buka',
    ];


    public function kantin_pengajuan()
    {
        return $this->hasMany(KantinPengajuan::class, 'kantin_id');
    }

    public function kantin_produk()
    {
        return $this->hasMany(KantinProduk::class, 'kantin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
