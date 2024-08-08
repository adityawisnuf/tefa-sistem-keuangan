<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KantinProdukKategori extends Model
{
    use HasFactory;

    protected $table = 'kantin_produk_kategori';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];

    public function kantin_produk()
    {
        return $this->hasMany(KantinProduk::class, 'kantin_produk_kategori_id');
    }
}
