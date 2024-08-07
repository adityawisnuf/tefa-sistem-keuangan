<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KantinProduk extends Model
{
    use HasFactory;

    
    protected $table = 'kantin_produk';

    protected $fillable = [
        'kantin_id',
        'kantin_produk_kategori_id',
        'nama_produk',
        'foto_produk',
        'deskripsi',
        'harga',
        'stok',
        'status',        
    ];

    public function kantin()
    {
        return $this->belongsTo(Kantin::class, 'kantin_id');
    }

    public function kantin_transaksi()
    {
        return $this->hasMany(KantinTransaksi::class, 'kantin_produk_id');
    }
}
