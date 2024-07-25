<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsetSekolah extends Model
{
    use HasFactory;

    protected $table= 'aset';

    protected $fillable= [
        'nama', 'kondisi', 'penggunaan',
    ];
}
