<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryTransaksiKiloan extends Model
{
    use HasFactory;

    protected $table = 'laundry_transaksi_kiloan';

    protected $fillable = [
        'siswa_id',
        'laundry_id',
        'laundry_layanan_id',
        'berat',
        'harga',
        'harga_total',
        'status',
        'tanggal_pemesanan',
        'tanggal_selesai',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function laundry_layanan()
    {
        return $this->belongsTo(LaundryLayanan::class, 'laundry_layanan_id');
    }
    public function laundry()
    {
        return $this->belongsTo(Laundry::class, 'laundry_id');
    }
}
