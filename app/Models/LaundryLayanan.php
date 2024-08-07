<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryLayanan extends Model
{
    use HasFactory;

    protected $table = 'laundry_layanan';

    protected $fillable = [
        'laundry_id',
        'nama_layanan',
        'deskripsi',
        'harga_per_kilo',
        'status',

    ];

    public function laundry()
    {
        return $this->belongsTo(Laundry::class, 'laundry_id');
    }
    
    public function laundry_transaksi_kiloan()
    {
        return $this->hasMany(LaundryTransaksiKiloan::class, 'laundry_layanan_id');
    }

}
