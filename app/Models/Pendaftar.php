<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendaftar extends Model
{
    use HasFactory;

    protected $table='pendaftar';

    protected $fillable= [
        'ppdb_id', 
        'nama_depan', 
        'nama_belakang', 
        'jenis_kelamin', 
        'tempat_lahir', 
        'tgl_lahir', 
        'alamat', 
        'village_id', 
        'nama_ayah', 
        'nama_ibu', 
        'tgl_lahir_ayah', 
        'tgl_lahir_ibu',
    ];

    public function ppdb()
    {
        return $this->belongsTo(Ppdb::class, 'ppdb_id');
    }
}
