<?php

namespace App\Models;

use App\Enums\TagType;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Tag extends Model
{
    use HasTranslations;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'type',
    ];

    public array $translatable = [
        'name',
    ];

    protected function casts(): array
    {
        return [
            'type' => TagType::class,
        ];
    }
}
