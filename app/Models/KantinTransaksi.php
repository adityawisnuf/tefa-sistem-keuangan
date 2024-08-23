<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KantinTransaksi extends Model
{
    use HasFactory;

    protected $table = 'kantin_transaksi';

    protected $fillable = [
        'siswa_id',
        'usaha_id',
        'status',
        'tanggal_pemesanan',
        'tanggal_selesai',
    ];


    public function kantin_transaksi_detail()
    {
        return $this->hasMany(KantinTransaksiDetail::class, 'kantin_transaksi_id');
    }

    public function usaha()
    {
        return $this->belongsTo(Usaha::class, 'usaha_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    // public function getHargaTotalAttribute()
    // {
    //     return $this->kantin_transaksi_detail->sum(function ($detail) {
    //         return $detail->harga * $detail->jumlah;
    //     });
    // }

}
