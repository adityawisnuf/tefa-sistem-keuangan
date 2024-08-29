<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KantinTransaksiDetail extends Model
{
    use HasFactory;

    protected $table = 'kantin_transaksi_detail';

    protected $fillable = [
        'kantin_produk_id',
        'kantin_transaksi_id',
        'jumlah',
        'harga',
    ];

    protected $appends = ['harga_total'];

    public function getHargaTotalAttribute()
    {
        return $this->jumlah * $this->harga;
    }
    public function kantin_transaksi()
    {
        return $this->belongsTo(KantinTransaksi::class, 'kantin_transaksi_id');
    }
    public function kantin_produk()
    {
        return $this->belongsTo(KantinProduk::class, 'kantin_produk_id');
    }
}
