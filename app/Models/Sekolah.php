<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    use HasFactory;

    protected $table = 'sekolah';

    protected $fillable = [
        'nama', 'alamat', 'telepon',
    ];

    public function kelas()
    {
        return $this->hasMany(kelas::class, 'sekolah_id');
    }
}
