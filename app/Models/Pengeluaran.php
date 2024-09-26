<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran';

    protected $fillable = [
        'pengeluaran_kategori_id', 'keperluan', 'nominal', 'diajukan_pada', 'disetujui_pada', 'status'
    ];

    public function pengeluaran_kategori()
    {
        return $this->hasOne(PengeluaranKategori::class, 'id', 'pengeluaran_kategori_id');
    }
}
