<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranKategori extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_kategori';

    protected $fillable = [
        'nama', 
        'jenis_pembayaran', 
        'tanggal_pembayaran', 
        'status',
    ];

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'pembayaran_kategori_id');
    }
}
