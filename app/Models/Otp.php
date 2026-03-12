<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'user_type',
        'type',
        'email',
        'code',
        'expires_at',
    ];
}
