<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderItemRepository extends BaseRepository
{
    public function __construct(OrderItem $model)
    {
        parent::__construct($model);
    }

    public function getByItemNo(string $itemNo)
    {
        return $this->model->where('item_no', $itemNo)->first();
    }

    public function getTopItems()
    {
        $sortBy = request('sort_by', 'quantity_sold');
        $sortOrder = request('sort_order', 'DESC');

        $sortColumn = match ($sortBy) {
            'quantity_sold' => 'quantity_sold',
            'net_revenue'   => 'net_revenue',
            'total_profit'  => 'total_profit',
            default         => 'quantity_sold',
        };

        return $this->model
            ->with('product', 'variantValue')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('product_variant_values', 'product_variant_values.id', '=', 'order_items.product_variant_value_id')
            ->whereNotIn('orders.status', [OrderStatus::WAITING_PAYMENT->value, OrderStatus::CANCELED->value])
            ->when(request('category_id'), function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->where('category_id', request('category_id'));
                });
            })
            ->when(request('from_date'), fn($query) => $query->whereDate('orders.created_at', '>=', request('from_date')))
            ->when(request('to_date'), fn($query) => $query->whereDate('orders.created_at', '<=', request('to_date')))
            ->when(request('creation_range'), function ($query, $period) {
                match ($period) {
                    'today' => $query->whereDate('orders.created_at', now()),
                    'last_7_days' => $query->whereDate('orders.created_at', '>=', now()->subDays(7)),
                    'last_30_days' => $query->whereDate('orders.created_at', '>=', now()->subDays(30)),
                    'this_month' => $query->whereMonth('orders.created_at', now()->month)->whereYear('orders.created_at', now()->year),
                    default => null
                };
            })
            ->select([
                'order_items.product_id',
                'order_items.product_variant_value_id',

                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.total) as revenue'),
                DB::raw('SUM(order_items.total - (order_items.user_facing_price * order_items.fulfilled_quantity)) as net_revenue'),
                DB::raw('SUM(order_items.quantity * (order_items.user_facing_price - COALESCE(order_items.provider_original_price, order_items.price))) as total_profit'),
            ])
            ->groupBy('order_items.product_id', 'order_items.product_variant_value_id')
            ->orderByRaw("$sortColumn $sortOrder")
            ->limit(10)
            ->get();
    }
}
