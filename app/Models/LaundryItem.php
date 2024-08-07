<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryItem extends Model
{
    use HasFactory;
    protected $table = 'laundry_item';

    protected $fillable = [
        'laundry_id',
        'nama_item',
        'deskripsi',
        'harga',
        'status',
    ];
    
    public function laundry()
    {
        return $this->belongsTo(Laundry::class, 'laundry_id');
    }
    
    public function laundry_item_detail()
    {
        return $this->hasMany(LaundryItemDetail::class, 'laundry_item_id');
    }
}
