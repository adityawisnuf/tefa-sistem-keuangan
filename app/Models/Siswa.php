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
        'nama_lengkap', 
        'alamat', 
        'tanggal_lahir', 
        'telepon', 
        'kelas_id', 
        'orangtua_id',
    ];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function Orangtua()
    {
        return $this->belongsTo(Orangtua::class, 'orangtua_id');
    }

    public function Pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'siswa_id');
    }
}
