<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryTransaksi extends Model
{
    use HasFactory;

    protected $table = 'laundry_transaksi';

    protected $fillable = [
        'laundry_id', 'qty', 'total_harga', 'merchant_order_id',
    ];

    public function pembayaran_duitku()
    {
        return $this->belongsTo(PembayaranDuitku::class, 'merchant_order_id');
    }

    public function laundry()
    {
        return $this->belongsTo(Laundry::class, 'laundry_id');
    }
}
