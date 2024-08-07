<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaundryPengajuan extends Model
{
    use HasFactory;

    protected $table = 'laundry_pengajuan';

    protected $fillable = [
        'laundry_id',
        'jumlah_pengajuan',
        'status',
        'alasan_penolakan',
        'tanggal_pengajuan',
        'tanggal_selesai',
    ];

    public function laundry()
    {
        return $this->belongsTo(Laundry::class, 'laundry_id');
    }
}
