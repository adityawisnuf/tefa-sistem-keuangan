<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ppdb extends Model
{
    use HasFactory;

    // Kolom yang diizinkan untuk diisi
protected $table = "ppdb";

    protected $fillable = [
        'status',
        'merchant_order_id',
        'created_at',
        'updated_at'
    ];

    // Optional: Jika Anda ingin mendefinisikan tipe kolom secara lebih eksplisit
    protected $casts = [
        'status' => 'integer', // 1, 2, 3
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
