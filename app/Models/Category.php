<?php

namespace App\Models;

use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasTranslations;

    protected $fillable = [
        'external_id',
        'name',
        'type',
        'parent_id',
        'short_description',
        'description',
        'is_active'
    ];

    public array $translatable = [
        'name',
        'short_description',
        'description'
    ];



    protected function casts(): array
    {
        return [
            'type' => CategoryType::class,
            'is_active' => 'boolean'
        ];
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getImageAttribute()
    {
        return $this->getMedia('*')->first();
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable', 'model_tag');
    }

}
