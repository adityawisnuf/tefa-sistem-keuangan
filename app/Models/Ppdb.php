<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ppdb extends Model
{
    use HasFactory;

    protected $table = 'ppdb';

    protected $fillable = [
        'user_id',
        'status',
        'merchant_order_id',
    ];

    // Relasi dengan PembayaranDuitku
    public function pembayaranDuitku()
    {
        return $this->hasOne(PembayaranDuitku::class, 'merchant_order_id', 'merchant_order_id');
    }

    // Relasi dengan PendaftarAkademik
    public function pendaftaranAkademik()
    {
        return $this->hasOne(PendaftarAkademik::class, 'ppdb_id');
    }       

    // Relasi dengan Pendaftar
    public function pendaftar()
    {
        return $this->hasOne(Pendaftar::class, 'ppdb_id');
    }

    // Relasi dengan PendaftarDokumen
    public function pendaftarDokumen()
    {
        return $this->hasOne(PendaftarDokumen::class, 'ppdb_id');
    }

    // Relasi dengan PembayaranPpdb
    public function pembayaranPpdb()
    {
        return $this->hasOne(PembayaranPpdb::class, 'ppdb_id');
    }

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
