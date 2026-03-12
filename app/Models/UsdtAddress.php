<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsdtAddress extends Model
{
    protected $fillable = [
        'user_id',
        'network_identifier',
        'address',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
