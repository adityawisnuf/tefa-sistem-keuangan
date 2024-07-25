<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Laundry extends Model
{
    use HasFactory;

    protected $table= 'laundry';

    protected $fillable = [
        'berat', 'harga',
    ];

    public function laundry_transaksi () 
    {
        return $this->hasMany(LaundryTransaksi::class, 'laundry_id');
    }
}
