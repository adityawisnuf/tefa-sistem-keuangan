<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSiswa extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_siswa';

    protected $fillable = [
        'siswa_id',
        'pembayaran_id',
        'nominal',
        'merchant_order_id',
        'status',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function pembayaran_kategori()
    {
        return $this->belongsTo(PembayaranKategori::class, 'pembayaran_kategori_id');
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
    }

    public function pembayaran_siswa_cicilan()
    {
        return $this->hasMany(PembayaranSiswaCicilan::class, 'pembayaran_siswa_id');
    }
    public function satu_duitku()
    {
        return $this->hasOne(PembayaranDuitku::class, 'merchant_order_id', 'merchant_order_id');
    }
    
}
