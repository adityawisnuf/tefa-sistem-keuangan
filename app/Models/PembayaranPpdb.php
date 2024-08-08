<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranPpdb extends Model
{
    use HasFactory;


    protected $fillable = [
        'ppdb_id',
        'pembayaran_id',
        'nominal',
        'merchant_order_id',
        'status',
    ];


    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
    }

    public function pembayaran_duitku()
    {
        return $this->belongsTo(PembayaranDuitku::class, 'merchant_order_id');
    }

    public function ppdb()
    {
        return $this->belongsTo(Ppdb::class, 'ppdb_id');
    }




}
