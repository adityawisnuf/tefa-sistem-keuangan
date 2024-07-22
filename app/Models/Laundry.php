<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laundry extends Model
{
    use HasFactory, SoftDeletes;

    protected $table= 'laundry';

    protected $fillable = [
        'berat', 'harga',
    ];

    public function laundry_transaksi ()
    {
        return $this->hasMany(LaundryTransaksi::class, 'laundry_id');
    }
}
