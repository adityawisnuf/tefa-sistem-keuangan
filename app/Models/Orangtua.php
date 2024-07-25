<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orangtua extends Model
{
    use HasFactory;

    protected $table = 'orangtua';

    protected $fillable = [
        'user_id', 
        'nama',
    ];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Siswa()
    {
        return $this->hasMany(Siswa::class, 'orangtua_id');
    }
}
