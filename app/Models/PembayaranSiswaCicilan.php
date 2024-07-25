<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSiswaCicilan extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_siswa_cicilan';

    protected $fillable = [
        'pembayaran_siswa_id',
        'nominal_cicilan',
        'status',
    ];

    public function Pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_siswa_id');
    }
}
