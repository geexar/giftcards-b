<?php

namespace App\Models;

use App\Enums\CategorySource;
use App\Enums\CategoryType;
use App\Services\Admin\CategoryService;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'source',
        'external_id',
        'name',
        'type',
        'parent_id',
        'short_description',
        'description',
        'is_active',
        'is_promoted',
        'is_featured',
        'is_trending',
    ];

    public array $translatable = [
        'name',
        'short_description',
        'description'
    ];


    protected function casts(): array
    {
        return [
            'source' => CategorySource::class,
            'type' => CategoryType::class,
            'is_promoted' => 'boolean',
            'is_featured' => 'boolean',
            'is_trending' => 'boolean',
            'is_active' => 'boolean',
            'is_parents_active' => 'boolean',
        ];
    }


    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function childs()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function grandchilds()
    {
        return $this->hasManyThrough(Category::class, Category::class, 'parent_id', 'parent_id', 'id', 'id');
    }

    public function getImageAttribute()
    {
        return $this->getMedia('*')->first();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeWithActiveParent($query)
    {
        return $query->where(function ($q) {
            // Either no parent, or parent is active
            $q->whereDoesntHave('parent')
                ->orWhereHas('parent', function ($q2) {
                    $q2->where('is_active', true)
                        // Optional: check grandparent as well
                        ->where(function ($q3) {
                            $q3->whereDoesntHave('parent')
                                ->orWhereHas('parent', function ($q4) {
                                    $q4->where('is_active', true);
                                });
                        });
                });
        });
    }

    public function getTotalProductsCountAttribute(): int
    {
        return app(CategoryService::class)->getTotalProductsCountInCategory($this->id);
    }
}
