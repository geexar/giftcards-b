<?php

namespace App\Services\User;

use App\Enums\DeliveryType;
use App\Models\OrderItem;
use App\Repositories\ProductRepository;
use App\Repositories\ProductVariantValueRepository;
use App\Repositories\OrderItemCodeRepository;

class OrderStockReservationService
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductVariantValueRepository $productVariantValueRepository,
        private OrderItemCodeRepository $orderItemCodeRepository
    ) {}

    public function reserveStock(OrderItem $orderItem)
    {
        $itemable = $orderItem->item;

        if ($orderItem->delivery_type == DeliveryType::INSTANT) {
            $codesToReserve = $itemable->purchasableCodes()
                ->limit($orderItem->quantity)
                ->lockForUpdate()
                ->get();

            foreach ($codesToReserve as $code) {
                $code->update(['reserved_at' => now()]);

                // Attach code to order item
                $this->orderItemCodeRepository->create([
                    'order_item_id' => $orderItem->id,
                    'code_id' => $code->id
                ]);
            }
        } else {
            // Manual stock
            $stockable = $this->resolveStockableForUpdate($orderItem);

            $stockable->increment('reserved_stock', $orderItem->quantity);
        }
    }

    public function releaseStock(OrderItem $orderItem)
    {
        if ($orderItem->delivery_type == DeliveryType::INSTANT) {
            // Release codes attached to this order item
            foreach ($orderItem->codes as $code) {
                $code->update(['reserved_at' => null]);
                $this->orderItemCodeRepository->delete($orderItem->id, $code->id);
            }
        } else {
            // Manual stock
            $stockable = $this->resolveStockableForUpdate($orderItem);

            // Decrement reserved stock and prevent negative value
            $stockable->update([
                'reserved_stock' => max($stockable->reserved_stock - $orderItem->quantity, 0)
            ]);
        }
    }

    public function resolveStockableForUpdate($orderItem)
    {
        if ($orderItem->product_variant_value_id) {
            return $this->productVariantValueRepository->getByIdForUpdate($orderItem->product_variant_value_id);
        }

        return $this->productRepository->getByIdForUpdate($orderItem->product_id);
    }
}
