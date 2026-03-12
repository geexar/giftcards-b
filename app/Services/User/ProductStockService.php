<?php

namespace App\Services\User;

use App\Enums\DeliveryType;
use App\Enums\ProductSource;
use App\Models\Product;
use App\Models\ProductVariantValue;

class ProductStockService
{
    /* ------------------------------
       PRODUCT STOCK
       ------------------------------ */
    public function getLocalProductTotalStock(Product $product): int
    {
        if ($product->source === ProductSource::API) {
            throw new \Exception('API products don\'t have total stock');
        }

        if ($product->has_variants) {
            $total = 0;

            foreach ($product->variant->requiresConfirmationValues as $value) {
                $total += $value->manual_stock;
            }

            foreach ($product->variant->instantValues as $value) {
                $total += $value->validCodes()->count();
            }

            return $total;
        }

        if ($product->delivery_type === DeliveryType::INSTANT) {
            return $product->validCodes()->count();
        }

        return $product->manual_stock;
    }

    public function getLocalProductAvailableStock(Product $product): int
    {
        if ($product->delivery_type === DeliveryType::INSTANT) {
            return $product->purchasableCodes()->count();
        }

        return $product->manual_stock - $product->reserved_stock;
    }

    public function localProductHasAvailableStock(Product $product): bool
    {
        return $this->getLocalProductAvailableStock($product) > 0;
    }

    /* ------------------------------
       VARIANT VALUE STOCK
       ------------------------------ */
    public function getVariantValueStock(ProductVariantValue $variantValue): int
    {
        if ($variantValue->delivery_type === DeliveryType::INSTANT) {
            return $variantValue->validCodes()->count();
        }

        return $variantValue->manual_stock;
    }

    public function getVariantValueAvailableStock(ProductVariantValue $variantValue): int
    {
        if ($variantValue->delivery_type === DeliveryType::INSTANT) {
            return $variantValue->purchasableCodes()->count();
        }

        return $variantValue->manual_stock - $variantValue->reserved_stock;
    }

    public function variantValueHasAvailableStock(ProductVariantValue $variantValue): bool
    {
        return $this->getVariantValueAvailableStock($variantValue) > 0;
    }
}
