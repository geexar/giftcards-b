<?php

namespace App\Services\Admin;

use App\Models\Product;
use App\Models\ProductVariantValue;
use InvalidArgumentException;

class MarkupFeeService
{
    /**
     * Calculate the markup fee for a product or a product variant.
     */
    public function calculateMarkupFee(Product $product, ?ProductVariantValue $variantValue = null, string $priceColumn = 'final_price')
    {
        // Validate that the price column is allowed
        if (!in_array($priceColumn, ['base_price', 'final_price'])) {
            throw new InvalidArgumentException('Invalid price column');
        }

        // Determine whether to use product-specific custom markup or default system settings
        if ($product->has_custom_markup_fee) {
            $type  = $product->custom_markup_fee_type;   // 'fixed' or 'percentage'
            $value = (float) $product->custom_markup_fee_value;
        } else {
            $type  = getSetting('markup_fee', 'markup_fee_type'); // fallback system-wide type
            $value = (float) getSetting('markup_fee', 'markup_fee_value'); // fallback system-wide value
        }

        // Decide whether to calculate based on variant price or product price
        $model = $variantValue ?? $product;

        // Calculate fee: fixed value or percentage of the price
        return $type == 'fixed'
            ? $value
            : ($value / 100) * $model->$priceColumn;
    }
}
