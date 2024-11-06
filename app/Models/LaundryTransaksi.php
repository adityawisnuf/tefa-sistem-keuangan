<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryTransaksi extends Model
{
    use HasFactory;

    protected $table = 'laundry_transaksi';

    protected $fillable = [
        'siswa_id',
        'usaha_id',
        'status',
        'tanggal_pemesanan',
        'tanggal_selesai',
    ];

    public function laundry_transaksi_detail ()
    {
        return $this->hasMany(LaundryTransaksiDetail::class, 'laundry_transaksi_id');
    }

    public function usaha()
    {
        return $this->belongsTo(Usaha::class, 'usaha_id');
    }

    public function siswa ()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

}
