<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;


class Pendaftar extends Model
{
    use HasFactory, Notifiable;

    protected $table='pendaftar';

    protected $fillable= [
        'ppdb_id',
        'nama_depan',
        'nama_belakang',
        'jenis_kelamin',
        'nik',
        'email',
        'nisn',
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
    public function village()
    {
        return $this->belongsTo(Village::class, 'village_id');
    }
}
