<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pemasukan extends Model
{
    use HasFactory;

    protected $table='pemasukan';

    protected $fillable=[
    'sekolah',
    'kelas',
    'siswa',
    'orangtua',
    'user',
    'pembayaran',
    'pembayaran_siswa',
    'pembayaran_siswa_cicilan',
    'pembayaran_kategori',
    'pembayaran_duitku',
    ];
    



}



