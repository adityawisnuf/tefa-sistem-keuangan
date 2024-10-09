<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelOtp extends Model
{
    use HasFactory;

    protected $table= 'otps';

    protected $fillable = [
        'identifier', 'token', 'validity', 'valid'
    ];
}

