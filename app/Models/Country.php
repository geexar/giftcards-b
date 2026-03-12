<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Country extends Model 
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'code',
        'dial_code',
        'is_active',
    ];

    public array $translatable = [
        'name'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
