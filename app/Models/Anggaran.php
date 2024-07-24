<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggaran extends Model
{
    use HasFactory;

    protected $table = 'anggaran';

    protected $fillable = [
        'nama_anggaran', 'nominal', 'deskripsi', 'tanggal_pengajuan', 'target_terealisasikan', 'status', 'pengapprove', 'pengapprove_jabatan', 'catatan'
    ];
}
