<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengeluaranKategori extends Model
{
    use HasFactory;

    protected $table = 'pengeluaran_kategori';

    protected $fillable = [
        'nama', 'status',
    ];

    public function pengeluaran()
    {
        return $this->belongsTo(Pengeluaran::class, 'pengeluaran_kategori_id');
    }
}
