<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'pembayaran';

    protected $fillable = [
        'siswa_id',
        'pembayaran_kategori_id',
        'nominal',
        'status',
        'pembayaran_ke',
        'kelas_id',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function pembayaran_kategori()
    {
        // return $this->hasMany(PembayaranKategori::class, 'pembayaran_kategori_id');
        return $this->belongsTo(PembayaranKategori::class, 'pembayaran_kategori_id');
    }

    public function pembayaran_siswa()
    {
        return $this->hasMany(PembayaranSiswa::class, 'pembayaran_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
}
