<?php

namespace App\Services\User;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\Admin\StatusUpdateLogService;

class OrderStatusService
{
    public function __construct(private StatusUpdateLogService $statusUpdateLogService) {}
    public function detectOrderStatus(Order $order): OrderStatus
    {
        $statuses = $order->items()->pluck('status')->all();

        /*
        |--------------------------------------------------------------------------
        | - Strict All-Equal States
        |--------------------------------------------------------------------------
        */

        if ($this->allEqual($statuses, OrderItemStatus::COMPLETED)) {
            return OrderStatus::COMPLETED;
        }

        if ($this->allEqual($statuses, OrderItemStatus::PENDING_CONFIRMATION)) {
            return OrderStatus::PENDING_CONFIRMATION;
        }

        if ($this->allEqual($statuses, OrderItemStatus::PROCESSING)) {
            return OrderStatus::PROCESSING;
        }

        if ($this->allEqual($statuses, OrderItemStatus::FAILED)) {
            return OrderStatus::FAILED;
        }

        if ($this->allEqual($statuses, OrderItemStatus::CANCELED)) {
            return OrderStatus::CANCELED;
        }

        if ($this->allEqual($statuses, OrderItemStatus::REJECTED)) {
            return OrderStatus::REJECTED;
        }

        /*
        |--------------------------------------------------------------------------
        | - If ANY item is not final → ignore failed/rejected/canceled
        |--------------------------------------------------------------------------
        */

        $finalStates = [
            OrderItemStatus::COMPLETED,
            OrderItemStatus::PARTIALLY_FULFILLED,
            OrderItemStatus::FAILED,
            OrderItemStatus::CANCELED,
            OrderItemStatus::REJECTED,
        ];

        $hasNonFinal = false;

        foreach ($statuses as $status) {
            if (!in_array($status, $finalStates, true)) {
                $hasNonFinal = true;
                break;
            }
        }

        $workingStatuses = $statuses;

        if ($hasNonFinal) {
            $workingStatuses = array_values(
                array_filter(
                    $statuses,
                    fn($status) => !in_array($status, [
                        OrderItemStatus::FAILED,
                        OrderItemStatus::CANCELED,
                        OrderItemStatus::REJECTED,
                    ], true)
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | - Awaiting Action
        |--------------------------------------------------------------------------
        */

        if (
            $this->allIn($workingStatuses, [
                OrderItemStatus::PENDING_CONFIRMATION,
                OrderItemStatus::PROCESSING,
            ]) &&
            $this->containsAny($workingStatuses, [OrderItemStatus::PENDING_CONFIRMATION]) &&
            $this->containsAny($workingStatuses, [OrderItemStatus::PROCESSING])
        ) {
            return OrderStatus::AWAITING_ACTION;
        }

        /*
        |--------------------------------------------------------------------------
        | - Partially Completed
        |--------------------------------------------------------------------------
        */

        if (
            $this->containsAny($workingStatuses, [
                OrderItemStatus::COMPLETED,
                OrderItemStatus::PARTIALLY_FULFILLED,
            ]) &&
            $this->containsAny($workingStatuses, [
                OrderItemStatus::PENDING_CONFIRMATION,
                OrderItemStatus::PROCESSING,
            ])
        ) {
            return OrderStatus::PARTIALLY_COMPLETED;
        }

        /*
        |--------------------------------------------------------------------------
        | - Processed (All Final But Mixed)
        |--------------------------------------------------------------------------
        */

        if (!$hasNonFinal && $this->allIn($statuses, $finalStates)) {
            return OrderStatus::PROCESSED;
        }

        /*
        |--------------------------------------------------------------------------
        | - Fallback
        |--------------------------------------------------------------------------
        */

        if (!empty($workingStatuses)) {
            return $workingStatuses[0] === OrderItemStatus::PENDING_CONFIRMATION
                ? OrderStatus::PENDING_CONFIRMATION
                : OrderStatus::PROCESSING;
        }

        return OrderStatus::PROCESSING;
    }

    public function detectOrderItemStatus(OrderItem $orderItem): OrderItemStatus
    {
        // If nothing is fulfilled, mark as FAILED
        if ($orderItem->fulfilled_quantity == 0) {
            return OrderItemStatus::FAILED;
        }

        // If partially fulfilled, mark as PARTIALLY_FULFILLED
        if ($orderItem->quantity > $orderItem->fulfilled_quantity) {
            return OrderItemStatus::PARTIALLY_FULFILLED;
        }

        // If fully fulfilled, mark as COMPLETED
        return OrderItemStatus::COMPLETED;
    }

    // check if every value in $values exists in the allowed list
    private function allIn(array $values, array $allowed): bool
    {
        // Check if every value in $values exists in the allowed list
        foreach ($values as $value) {
            if (!in_array($value, $allowed)) {
                return false;
            }
        }
        return true;
    }

    // check if every value in $values is exactly equal to $match
    private function allEqual(array $values, $match): bool
    {
        foreach ($values as $value) {
            if ($value !== $match) {
                return false;
            }
        }
        return true;
    }


    // check if any value in $values exists in the allowed list
    private function containsAny(array $values, array $allowed): bool
    {
        // Check if at least one value in $values exists in the allowed list
        foreach ($values as $value) {
            if (in_array($value, $allowed)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Detects, updates, and logs the status of a single order item.
     */
    public function updateOrderItemStatus(OrderItem $orderItem, User|Admin|null $actor = null): void
    {
        $oldStatus = $orderItem->status;
        $newStatus = $this->detectOrderItemStatus($orderItem);

        $orderItem->update(['status' => $newStatus]);

        $this->statusUpdateLogService->store($orderItem, $oldStatus->value, $newStatus->value, $actor);
    }

    /**
     * Detects, updates, and logs the status of a single order.
     */
    public function updateOrderStatus(Order $order): void
    {
        $oldStatus = $order->status;
        $newStatus = $this->detectOrderStatus($order);

        $order->update(['status' => $newStatus]);

        if ($oldStatus !== $newStatus) {
            $this->statusUpdateLogService->store($order, $oldStatus->value, $newStatus->value);
        }
    }
}
