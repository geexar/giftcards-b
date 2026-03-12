<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Article extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'title',
        'body',
    ];

    public $translatable = [
        'title',
        'body',
    ];

    public function getImageAttribute()
    {
        return $this->getMedia('*')->first();
    }
}
