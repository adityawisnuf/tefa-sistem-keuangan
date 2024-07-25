<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiswaWallet extends Model
{
    use HasFactory;

    protected $table = 'siswa_wallet';

    protected $fillable = [
        'siswa_id', 'nominal',
    ];

    public function siswa ()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
    public function siswa_wallet_riwayat ()
    {
        return $this->hasMany(Siswa::class, 'siswa_wallet_id');
    }
}
