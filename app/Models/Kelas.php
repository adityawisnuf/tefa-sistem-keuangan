<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'sekolah_id', 
        'jurusan', 
        'kelas',
    ];

    public function Sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    public function Siswa()
    {
        return $this->hasMany(Siswa::class, 'kelas_id');
    }

    public function Pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'kelas_id');
    }
}
