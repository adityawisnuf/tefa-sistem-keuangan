<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryItemDetail extends Model
{
    use HasFactory;
    protected $table = 'laundry_item_detail';

    protected $fillable = [
        'laundry_item_id',
        'laundry_transaksi_satuan_id',
        'jumlah',
        'harga',
        'harga_total',
    ];
    
    public function laundry_item()
    {
        return $this->belongsTo(LaundryItem::class, 'laundry_item_id');
    }
    
    public function laundry_transaksi_satuan_id()
    {
        return $this->belongsTo(LaundryTransaksiSatuan::class, 'laundry_transaksi_satuan_id');
    }
}
