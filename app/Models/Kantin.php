<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kantin extends Model
{
    use HasFactory;

    protected $table= 'kantin';

    protected $fillable = [
        'nama_produk', 'deskripsi', 'harga', 'stok', 'status',
    ];

    public function kantin_transaksi () 
    {
        return $this->hasMany(KantinTransaksi::class, 'kantin_id');
    }
}
