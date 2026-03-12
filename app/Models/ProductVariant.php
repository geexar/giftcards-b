<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ProductVariant extends Model
{
    use SoftDeletes, HasTranslations;

    protected $fillable = [
        'product_id',
        'name',
    ];

    public array $translatable = [
        'name',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->hasMany(ProductVariantValue::class);
    }
}