<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table= 'siswa';

    protected $fillable= [
        'user_id', 'nama_depan', 'nama_belakang', 'alamat', 'tempat_lahir', 'telepon', 'kelas_id', 'orangtua_id',
    ];

    public function user ()
    {
        return $this->belongsTo(user::class, 'user_id');
    }

    public function orangtua ()
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
}
