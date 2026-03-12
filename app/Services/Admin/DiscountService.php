<?php

namespace App\Services\Admin;

class DiscountService
{
    public function calculateDiscount(float $price, string $discount_type, float $discount_value): float
    {
        if ($discount_type == 'fixed') {
            return $discount_value;
        }

        return ($discount_value / 100) * $price;
    }
}
