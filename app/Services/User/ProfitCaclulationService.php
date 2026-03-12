<?php

namespace App\Services\User;

use App\Models\Order;

class ProfitCaclulationService
{
    public function __construct() {}

    /**
     * Projected profit is calculated based on the configured markup rules,
     * regardless of whether the order is actually fulfilled or not.
     */
    public function getProjectedProfit(Order $order): float
    {
        return $order->items->sum(function ($item) {

            // Percentage-based markup (calculated per unit)
            if ($item->markup_fee_type == 'percentage') {
                $perUnitMarkup = ($item->price * $item->markup_fee_value) / 100;
                return $perUnitMarkup * $item->quantity;
            }

            // Fixed markup applied per unit
            return $item->markup_fee_value * $item->quantity;
        });
    }

    /**
     * Actual profit is calculated based on real cost vs selling price.
     */
    public function getActualProfit(Order $order): float
    {
        return $order->items->sum(function ($item) {

            // Determine the real cost of the product
            $productCost = $item->provider_original_price ?? $item->price;

            // Profit gained from selling one unit
            $perUnitProfit = $item->user_facing_price - $productCost;

            // Total profit for this order item
            return $perUnitProfit * $item->quantity;
        });
    }
}
