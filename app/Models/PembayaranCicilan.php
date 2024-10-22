<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranCicilan extends Model
{
    use HasFactory;

    // Nama tabel di database
    protected $table = 'pembayaran_cicilan';

    // Kolom yang bisa diisi
    protected $fillable = [
        'pembayaran_siswa_cicilan_id',
        'tanggal_pembayaran',
        'nominal_dibayar',
        'status',
        'transaction_response',
        'payment_method'
    ];

    // Jika ingin menambahkan relasi ke model lain
    // Misalnya relasi ke model PembayaranSiswaCicilan
    public function pembayaranSiswaCicilan()
    {
        return $this->belongsTo(PembayaranSiswaCicilan::class, 'pembayaran_siswa_cicilan_id');
    }
}
