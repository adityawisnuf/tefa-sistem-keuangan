<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Usaha extends Model
{
    use HasFactory;

    protected $table = 'usaha';

    protected $fillable = [
        'user_id',
        'nama_usaha',
        'alamat',
        'no_telepon',
        'no_rekening',
        'saldo',
        'status_buka',
    ];


    public function usaha_pengajuan()
    {
        return $this->hasMany(UsahaPengajuan::class, 'usaha_id');
    }

    public function kantin_produk()
    {
        return $this->hasMany(KantinProduk::class, 'usaha_id');
    }
    public function laundry_layanan()
    {
        return $this->hasMany(LaundryLayanan::class, 'usaha_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kantin_transaksi()
    {
        return $this->hasMany(KantinTransaksi::class, 'usaha_id');
    }

    public function laundry_transaksi()
    {
        return $this->hasMany(LaundryTransaksi::class, 'usaha_id');
    }
}
