<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KantinProduk extends Model
{
    use HasFactory, SoftDeletes;


    protected $table = 'kantin_produk';

    protected $fillable = [
        'usaha_id',
        'kantin_produk_kategori_id',
        'nama_produk',
        'foto_produk',
        'deskripsi',
        'harga_pokok',
        'harga_jual',
        'stok',
        'status',
    ];

    public function usaha()
    {
        return $this->belongsTo(Usaha::class, 'usaha_id');
    }

    public function kantin_transaksi_detail()
    {
        return $this->hasMany(KantinTransaksiDetail::class, 'kantin_produk_id');
        }
    public function kantin_produk_kategori()
    {
        return $this->belongsTo(KantinProdukKategori::class, 'kantin_produk_kategori_id');
    }

    protected function fotoProduk(): Attribute
    {
        return Attribute::make(
            get: function (string $value) {
                if (str_contains($value, 'http') || str_contains($value,'https')) return $value;
                return env('STORAGE_IMG_URL') . '/kantin/produk/' . $value;
            },
        );
    }
}
