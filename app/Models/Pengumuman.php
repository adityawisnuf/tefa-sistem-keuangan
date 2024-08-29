<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumuman';

    protected $fillable = [
        'status',
        'judul',
        'isi',
        'pesan_ditolak',
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo('users');
    }
}
