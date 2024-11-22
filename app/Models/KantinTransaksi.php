<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KantinTransaksi extends Model
{
    use HasFactory;

    protected $table = 'kantin_transaksi';

    protected $fillable = [
        'kantin_id', 'qty', 'total_harga', 'merchant_order_id',
    ];

    public function pembayaran_duitku()
    {
        return $this->belongsTo(PembayaranDuitku::class, 'merchant_order_id');
    }

    public function kantin()
    {
        return $this->belongsTo(Kantin::class, 'kantin_id');
    }
}
