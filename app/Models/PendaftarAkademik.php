<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendaftarAkademik extends Model
{
    use HasFactory;

    protected $table = 'pendaftar_akademik';

    protected $fillable = [
        'ppdb_id',
        'sekolah_asal',
        'tahun_lulus',  // Pastikan nama ini sama persis
        'jurusan_tujuan',
    ];

    public function ppdb()
    {
        return $this->belongsTo(Ppdb::class, 'ppdb_id');
    }
}
