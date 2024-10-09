<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsahaPengajuan extends Model
{
    use HasFactory;

    protected $table = 'usaha_pengajuan';

    protected $fillable = [
        'usaha_id',
        'jumlah_pengajuan',
        'status',
        'alasan_penolakan',
        'tanggal_pengajuan',
        'tanggal_selesai',
    ];

    public function usaha()
    {
        return $this->belongsTo(Usaha::class, 'usaha_id');
    }
}
