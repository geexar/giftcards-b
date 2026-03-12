<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_value_id',
        'quantity',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variantValue()
    {
        return $this->belongsTo(ProductVariantValue::class, 'product_variant_value_id');
    }

    public function getItemAttribute()
    {
        return $this->variantValue ?? $this->product;
    }
}
