<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryLayanan extends Model
{
    use HasFactory;

    protected $table = 'laundry_layanan';

    protected $fillable = [
        'usaha_id',
        'nama_layanan',
        'foto_layanan',
        'deskripsi',
        'harga',
        'tipe',
        'satuan',
        'status',

    ];

    public function usaha()
    {
        return $this->belongsTo(Usaha::class, 'usaha_id');
    }

    public function laundry_transaksi_detail()
    {
        return $this->hasMany(LaundryTransaksiDetail::class, 'laundry_transaksi_detail_id');
    }

}
