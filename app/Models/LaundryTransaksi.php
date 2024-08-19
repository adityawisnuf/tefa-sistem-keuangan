<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryTransaksi extends Model
{
    use HasFactory;

    protected $table = 'laundry_transaksi_detail';

    protected $fillable = [
        'siswa_id',
        'status',
        'tanggal_pemesanan',
        'tanggal_selesai',
    ];

    public function kantin_transaksi_detail ()
    {
        return $this->hasMany(LaundryLayanan::class, 'siswa_id');
    }
    public function siswa ()
    {
        return $this->belongsTo(LaundryLayanan::class, 'siswa_id');
    }
}
