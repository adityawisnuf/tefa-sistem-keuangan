<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendaftarDokumen extends Model
{
    use HasFactory;

    protected $table = 'pendaftar_dokumen';

    protected $fillable = [
        'ppdb_id', 'akte_kelahiran', 'kartu_keluarga', 'ijazah', 'raport',
    ];

    public function ppdb()
    {
        return $this->belongsTo(Ppdb::class, 'ppdb_id');
    }
}
