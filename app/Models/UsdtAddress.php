<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class UsdtAddress extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'user_id',
        'network_id',
        'address',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function network()
    {
        return $this->belongsTo(UsdtNetwork::class, 'network_id');
    }

    public function getQrcodeAttribute()
    {
        return $this->getMedia('*')->first();
    }
}
