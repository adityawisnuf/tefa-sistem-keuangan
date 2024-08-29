<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'orangtua_id',
        'kelas_id',
        'nama_depan',
        'nama_belakang',
        'alamat',
        'tempat_lahir',
        'tempat_lahir',
        'telepon',
        'kelas_id',
    ];

    protected $appends = ['nama_siswa'];

    public function getNamaSiswaAttribute()
    {
        return $this->nama_depan . ' ' . $this->nama_belakang;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orangtua()
    {
        return $this->belongsTo(Orangtua::class, 'orangtua_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function siswa_wallet()
    {
        return $this->hasOne(SiswaWallet::class, 'siswa_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'siswa_id');
    }

    public function village()
    {
        return $this->belongsTo(Village::class, 'village_id');
    }

    public function kantin_transaksi()
    {
        return $this->hasMany(KantinTransaksi::class, 'siswa_id');
    }

    public function laundry_transaksi()
    {
        return $this->hasMany(LaundryTransaksi::class, 'siswa_id');
    }

}
