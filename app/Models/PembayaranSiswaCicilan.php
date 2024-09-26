<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSiswaCicilan extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_siswa_cicilan';

    protected $fillable = [
        'pembayaran_siswa_id',
        'nominal_cicilan',
        'merchant_order_id',  // Tambahkan merchant_order_id
    ];

    public function pembayaran_siswa()
    {
        return $this->belongsTo(PembayaranSiswa::class, 'pembayaran_siswa_id');
    }

    public function pembayaran_duitku()
    {
        return $this->belongsTo(PembayaranDuitku::class, 'merchant_order_id');
    }
}
