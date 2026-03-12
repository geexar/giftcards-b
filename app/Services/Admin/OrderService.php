<?php

namespace App\Services\Admin;

use App\Enums\DeliveryType;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductSource;
use App\Http\Resources\Admin\OrderNotesUpdateResource;
use App\Http\Resources\Admin\RefundUpdateResource;
use App\Http\Resources\Admin\StatusUpdateLogResource;
use App\Jobs\FulfillApiOrderItemJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderNotesUpdate;
use App\Models\RefundUpdate;
use App\Models\StatusUpdateLog;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderNotesUpdateRepository;
use App\Repositories\OrderRepository;
use App\Services\User\OrderCommunicationService;
use App\Services\User\OrderFulfillmentService;
use App\Services\User\OrderStatusService;
use App\Services\User\RefundService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderItemRepository $orderItemRepository,
        private OrderFulfillmentService $orderFulfillmentService,
        private RefundService $refundService,
        private StatusUpdateLogService $statusUpdateLogService,
        private OrderStatusService $orderStatusService,
        private OrderCommunicationService $orderCommunicationService,
        private OrderNotesUpdateRepository $orderNotesUpdateRepository
    ) {}

    /**
     * Retrieve an order and ensure it exists and is not awaiting payment.
     */
    public function getOrder(string $id)
    {
        $order = $this->orderRepository->getById($id);

        if (!$order || in_array($order->status, [OrderStatus::WAITING_PAYMENT, OrderStatus::EXPIRED])) {
            throw new NotFoundHttpException('order not found');
        }

        return $order;
    }

    /**
     * Get all logs (status updates, refund updates, notes) for an order,
     * sorted by created_at descending and wrapped in their respective resources.
     */
    public function getOrderLogs(string $id)
    {
        $order = $this->getOrder($id);

        // 1. Collect order + order items status updates
        $statusUpdateLogs = $order->statusUpdateLogs
            ->merge($order->items->flatMap(fn($item) => $item->statusUpdateLogs));

        // 2. Collect refund updates if any
        $refundUpdates = $order->refund ? $order->refund->updates : collect();

        // 3. Collect note updates
        $noteUpdates = $order->notesUpdates;

        // 4. Merge all collections
        $logs = $statusUpdateLogs->merge($refundUpdates)->merge($noteUpdates);

        // 5. Sort by created_at descending
        $logs = $logs->sortByDesc('created_at')->values();

        // 6. Map each item to its proper resource
        $logs = $logs->map(function ($item) {
            return match (true) {
                $item instanceof StatusUpdateLog => new StatusUpdateLogResource($item),
                $item instanceof RefundUpdate => new RefundUpdateResource($item),
                $item instanceof OrderNotesUpdate => new OrderNotesUpdateResource($item),
            };
        });

        return $logs;
    }

    public function getFormattedLogsForPdf($orderId)
    {
        $logs = $this->getOrderLogs($orderId);

        return $logs->map(function ($resource) {
            $data = $resource->resolve();

            $actorName = 'System';
            $actorType = 'System';

            // Actor
            if (isset($data['actor'])) {
                $actorName = $data['actor']['name'] ?? 'System';
                $actorType = ucwords($data['actor_type'] ?? 'system');
            } elseif (isset($data['admin'])) {
                $actorName = $data['admin']['name'];
                $actorType = 'Admin';
            } elseif (isset($data['user'])) {
                $actorName = $data['user']['name'];
                $actorType = 'Customer';
            }

            $type = $data['type'] ?? null;

            // Use translations
            $description = match ($type) {
                'order_status_update' => __('logs.order_status_update', [
                    'old' => __(ucwords(str_replace('_', ' ', $data['old_status']))),
                    'new' => __(ucwords(str_replace('_', ' ', $data['new_status']))),
                ]),
                'order_item_status_update' => __('logs.order_item_status_update', [
                    'item' => $data['item_name'],
                    'old' => __(ucwords(str_replace('_', ' ', $data['old_status']))),
                    'new' => __(ucwords(str_replace('_', ' ', $data['new_status']))),
                ]),
                'refund_update' => __('logs.refund_update', [
                    'old' => __(ucwords(str_replace('_', ' ', $data['old_status']))),
                    'new' => __(ucwords(str_replace('_', ' ', $data['new_status']))),
                    'amount' => formatMoney($data['amount']),
                ]),
                'notes_update' => __('logs.notes_update', [
                    'content' => $data['content'] ?? '-',
                ]),
                default => __('logs.unknown_activity'),
            };

            return (object)[
                'actor_name' => $actorName,
                'actor_type' => $actorType,
                'description' => $description,
                'created_at' => $data['created_at'],
            ];
        });
    }

    /**
     * Retrieve an order item and validate that it can be approved.
     */
    public function getOrderItem(string $orderItemId)
    {
        $orderItem = $this->orderItemRepository->getById($orderItemId);

        if (!$orderItem || in_array($orderItem->order->status, [OrderStatus::WAITING_PAYMENT, OrderStatus::EXPIRED])) {
            throw new NotFoundHttpException('order item not found');
        }

        return $orderItem;
    }


    public function update(string $id, array $data)
    {
        $order = $this->getOrder($id);

        // Validate order update
        $this->validateOrderUpdate($order, $data);

        foreach ($data['items'] as $item) {
            $orderItem = $this->orderItemRepository->getById($item['id']);

            // handle rejection
            if ($item['status'] == 'rejected') {
                $this->reject($orderItem, $item['rejection_reason'] ?? null);
                continue;
            }

            // handle approval
            if ($orderItem->product->source == ProductSource::LOCAL) {
                $this->approveLocalItem($orderItem, $item['codes']);
            } else {
                $this->approveApiItem($orderItem);
            }

            // set processed by
            $order->update([
                'processed_by' => auth('admin')->id(),
                'processed_at' => now()
            ]);
        }
    }

    private function validateOrderUpdate(Order $order, array $data)
    {
        foreach ($data['items'] as $item) {
            $orderItem = $this->orderItemRepository->getById($item['id']);

            if (!$orderItem || $orderItem->order_id != $order->id) {
                throw new NotFoundHttpException('order item not found');
            }

            if ($orderItem->delivery_type != DeliveryType::REQUIRES_CONFIRMATION) {
                throw new NotFoundHttpException('only items with requires confirmation delivery type can be approved');
            }

            if ($orderItem->status != OrderItemStatus::PENDING_CONFIRMATION) {
                throw new NotFoundHttpException('order item not pending confirmation');
            }
        }
    }

    /**
     * Approve a local product order item and fulfill it.
     */
    public function approveLocalItem(OrderItem $orderItem, array $codes)
    {
        if ($orderItem->product->source != ProductSource::LOCAL) {
            throw new NotFoundHttpException('not a local product');
        }

        $this->orderFulfillmentService->fulfillConfirmedLocalItem($orderItem, $codes, auth('admin')->user());
    }

    /**
     * Approve an API product order item, update its status to PROCESSING,
     * update and log order status, dispatch fulfillment job, and handle refunds.
     */
    public function approveApiItem(OrderItem $orderItem)
    {
        if ($orderItem->product->source != ProductSource::API) {
            throw new NotFoundHttpException('not an api product');
        }

        // Capture old order status
        $order = $orderItem->order;
        $oldOrderStatus = $order->status;

        DB::transaction(function () use ($orderItem, $order) {
            // Update and log order item status
            $orderItem->update(['status' => OrderItemStatus::PROCESSING]);
            $this->statusUpdateLogService->store($orderItem, OrderItemStatus::PENDING_CONFIRMATION->value, OrderItemStatus::PROCESSING->value, auth('admin')->user());

            // Update and log order status
            $this->orderStatusService->updateOrderStatus($order->refresh());

            // Dispatch API fulfillment job
            FulfillApiOrderItemJob::dispatch($orderItem);
        });

        // Notify order updates if needed
        $this->orderCommunicationService->notifyOrderUpdates($order->refresh(), $oldOrderStatus);
    }

    /**
     * Reject an order item and update its status with proper logging.
     */
    public function reject(OrderItem $orderItem, ?string $rejectionReason = null)
    {
        // Caputre old order status
        $order = $orderItem->order;
        $oldOrderStatus = $order->status;

        DB::transaction(function () use ($orderItem, $order, $rejectionReason) {
            $orderItem->update([
                'status' => OrderItemStatus::REJECTED,
                'fulfilled_quantity' => 0,
                'rejection_reason' => $rejectionReason
            ]);

            // Update and log order item status
            $this->statusUpdateLogService->store($orderItem, OrderItemStatus::PENDING_CONFIRMATION->value, OrderItemStatus::REJECTED->value);

            // Update and log order status
            $this->orderStatusService->updateOrderStatus($order->refresh());

            // handle quantity
            $this->orderFulfillmentService->handleReturnedStock($orderItem);

            // Handle refund if applicable
            $this->refundService->handleRefund($order->refresh());
        });

        // Notify order updates if needed
        $this->orderCommunicationService->notifyOrderUpdates($order->refresh(), $oldOrderStatus);
    }

    /**
     * Update the order's notes and create a corresponding note update record.
     */
    public function updateNotes(string $id, ?string $notes)
    {
        $order = $this->getOrder($id);

        DB::transaction(function () use ($order, $notes) {
            $order->update(['notes' => $notes]);

            $this->orderNotesUpdateRepository->create([
                'order_id' => $order->id,
                'admin_id' => auth('admin')->id(),
                'content' => $notes,
                'created_at' => now()
            ]);
        });
    }
}
