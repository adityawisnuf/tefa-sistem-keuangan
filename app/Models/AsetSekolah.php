<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsetSekolah extends Model
{
    use HasFactory;

    protected $table= 'aset';

    protected $fillable= [
        'tipe', 'nama', 'harga', 'kondisi', 'penggunaan',
    ];

    public function getKondisiText()
    {   
        switch ($this->kondisi) {
            case 1:
                return 'Baik';
            case 2:
                return 'Kurang Baik';
            case 3:
                return 'Buruk';
            default:
                return 'Status tidak ditemukan';
        }
    }

    public function getTipeText()
    {   
        switch ($this->tipe) {
            case 1:
                return 'Asset Tetap';
            case 2:
                return 'Asset Lancar';
            default:
                return 'Status tidak ditemukan';
        }
    }
}
