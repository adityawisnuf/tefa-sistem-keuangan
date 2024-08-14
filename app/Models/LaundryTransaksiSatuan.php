<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryTransaksiSatuan extends Model
{
    use HasFactory;

    protected $table = 'laundry_transaksi_satuan';

    protected $fillable = [
        'siswa_id',
        'laundry_id',
        'laundry_item_detail_id',
        'jumlah_item',
        'harga_total',
        'status',
        'tanggal_pemesanan',
        'tanggal_selesai',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function laundryItemDetail()
    {
        return $this->belongsTo(LaundryItemDetail::class, 'laundry_item_detail_id');
    }
    public function laundry()
    {
        return $this->belongsTo(Laundry::class, 'laundry_id');
    }
}
