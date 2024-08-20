<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryTransaksiDetail extends Model
{
    use HasFactory;

    protected $table = 'laundry_transaksi_detail';

    protected $fillable = [
        'laundry_layanan_id',
        'laundry_transaksi_id',
        'jumlah',
        'harga',
    ];

    public function laundry_layanan ()
    {
        return $this->belongsTo(LaundryLayanan::class, 'laundry_layanan_id');
    }

    public function laundry_transaksi ()
    {
        return $this->belongsTo(LaundryTransaksi::class, 'laundry_transaksi_id');
    }
}
