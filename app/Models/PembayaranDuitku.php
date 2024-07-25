<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranDuitku extends Model
{
    use HasFactory;

    protected $table= 'pembayaran_duiku';

    protected $fillable = [
        'merchant_order_id', 'reference', 'payment_method', 'transaction_response', 'callback_response', 'status',
    ];

    public function Ppdb() 
    {
        return $this->hasOne(Ppdb::class, 'merchant_order_id');
    }

}
