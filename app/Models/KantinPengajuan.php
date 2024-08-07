<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KantinPengajuan extends Model
{
    use HasFactory;

    protected $table = 'kantin_pengajuan';

    protected $fillable = [
        'kantin_id',
        'jumlah_pengajuan',
        'status',
        'alasan_penolakan',
        'tanggal_pengajuan',
        'tanggal_selesai',
    ];

    public function kantin()
    {
        return $this->belongsTo(Kantin::class, 'kantin_id');
    }
}
