<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orangtua extends Model
{
    use HasFactory;

    protected $table = 'orangtua';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'nama',
    ];

    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'orangtua_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
