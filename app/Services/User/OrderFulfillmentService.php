<?php

namespace App\Services\User;

use App\Enums\DeliveryType;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductSource;
use App\Jobs\FulfillApiOrderItemJob;
use App\Models\Admin;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Repositories\CodeRepository;
use App\Repositories\OrderItemCodeRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProductVariantValueRepository;
use App\Services\Admin\StatusUpdateLogService;
use App\Services\Lirat\LiratGiftCardService;
use App\Services\User\OrderCommunicationService;
use App\Services\User\RefundService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderFulfillmentService
{
    public function __construct(
        private OrderStatusService $orderStatusService,
        private LiratGiftCardService $liratGiftCardService,
        private CodeRepository $codeRepository,
        private RefundService $refundService,
        private StatusUpdateLogService $statusUpdateLogService,
        private ProductRepository $productRepository,
        private ProductVariantValueRepository $productVariantValueRepository,
        private OrderCommunicationService $orderCommunicationService,
        private OrderItemCodeRepository $orderItemCodeRepository,
        private OrderStockReservationService $orderStockReservationService
    ) {}

    public function process(Order $order)
    {
        // make order status processing as the initial state
        $order->update(['status' => OrderStatus::PROCESSING]);

        // Mark instant items as processing
        $this->markInstantItemsProcessing($order);

        foreach ($order->items as $orderItem) {
            if ($orderItem->delivery_type == DeliveryType::REQUIRES_CONFIRMATION) {
                $this->processConfirmationItem($orderItem);
                continue;
            }

            if ($orderItem->delivery_type == DeliveryType::INSTANT) {
                if ($orderItem->product->source == ProductSource::API) {
                    FulfillApiOrderItemJob::dispatch($orderItem);
                } else {
                    $this->fulfillInstantLocalItem($orderItem);
                }
            }
        }

        // Update order status
        $this->orderStatusService->updateOrderStatus($order);
    }

    private function processConfirmationItem(OrderItem $orderItem)
    {
        $orderItem->update(['status' => OrderItemStatus::PENDING_CONFIRMATION]);

        // release stock
        $this->orderStockReservationService->releaseStock($orderItem);

        // decrease item stock
        $stockable = $this->orderStockReservationService->resolveStockableForUpdate($orderItem);
        $stockable->decrement('manual_stock', $orderItem->quantity);

        $this->statusUpdateLogService->store($orderItem, null, OrderItemStatus::PENDING_CONFIRMATION->value);
    }

    public function fulfillApiItem(OrderItem $orderItem)
    {
        $fulfilledQuantity = 0;

        for ($i = 0; $i < $orderItem->quantity; $i++) {
            try {
                // check if product is available
                $apiProduct = $this->liratGiftCardService->getProduct($orderItem->product->external_id);
                if (!$apiProduct->available) {
                    Log::error('API product is not available', ['order_id' => $orderItem->order_id, 'order_item_id' => $orderItem->id]);
                    continue;
                }

                $referenceId = $this->generateReferenceId();
                $response = $this->liratGiftCardService->createOrder($orderItem->product->external_id, $referenceId);

                if (!$response->successful() || !$response->json()['serials']) {
                    Log::error('API request failed', ['order_id' => $orderItem->order_id, 'order_item_id' => $orderItem->id, 'status' => $response->status(), 'body' => $response->body()]);
                    continue;
                }

                $serial = $response->json()['serials'][0];

                // create code
                $code = $orderItem->item->codes()->create([
                    'reference_id' => $referenceId,
                    'raw_response' => $response->body(),
                    'code' => $serial['serialCode'],
                    'expiry_date' => isset($serial['validTo']) ? Carbon::createFromFormat('d/m/Y', $serial['validTo'])->format('Y-m-d') : null,
                    'source' => ProductSource::API,
                    'is_used' => true
                ]);

                // attach code to order item
                $this->orderItemCodeRepository->create([
                    'order_item_id' => $orderItem->id,
                    'code_id' => $code->id
                ]);

                $fulfilledQuantity++;
            } catch (\Exception $e) {
                Log::error('API Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                continue;
            }
        }

        $this->finalizeFulfillment($orderItem, $fulfilledQuantity);
    }

    public function fulfillInstantLocalItem(OrderItem $orderItem)
    {
        DB::transaction(function () use ($orderItem) {
            $purchasedCodes = $orderItem->codes;

            Log::info('Purchased codes', ['order_item_id' => $orderItem->id, 'codes' => $purchasedCodes]);

            foreach ($purchasedCodes as $code) {
                $code->update(['is_used' => true]);
            }

            $fulfilledQuantity = $purchasedCodes->count();

            $this->finalizeFulfillment($orderItem, $fulfilledQuantity);
        });
    }

    public function fulfillConfirmedLocalItem(OrderItem $orderItem, array $codes, Admin $actor)
    {
        DB::transaction(function () use ($orderItem, $codes, $actor) {
            foreach ($codes as $codeData) {
                $code = $orderItem->item->codes()->create(array_merge($codeData, ['is_used' => true]));

                $this->orderItemCodeRepository->create([
                    'order_item_id' => $orderItem->id,
                    'code_id' => $code->id
                ]);
            }

            $fulfilledQuantity = count($codes);
            $unfulfilledQuantity = $orderItem->quantity - $fulfilledQuantity;

            if ($unfulfilledQuantity > 0) {
                $this->restockUnfulfilledLocalStock($orderItem, $unfulfilledQuantity);
            }

            $this->finalizeFulfillment($orderItem, $fulfilledQuantity, $actor);
        });
    }

    public function restockUnfulfilledLocalStock(OrderItem $orderItem, int $unfulfilledQuantity): void
    {
        // Only handle confirmation items
        if ($orderItem->delivery_type !== DeliveryType::REQUIRES_CONFIRMATION) {
            return;
        }

        // Fetch product with a lock
        $product = $this->productRepository->getByIdForUpdate($orderItem->product_id);

        // Determine stockable (variant or product)
        $stockable = $orderItem->product_variant_value_id
            ? $this->productVariantValueRepository->getByIdForUpdate($orderItem->product_variant_value_id)
            : $product;

        // Increment manual stock
        $stockable->increment('manual_stock', $unfulfilledQuantity);
    }


    /**
     * Shared logic to finalize the fulfillment state of an OrderItem.
     */
    private function finalizeFulfillment(OrderItem $orderItem, int $fulfilledQuantity, User|Admin|null $actor = null)
    {
        // Capture old order status
        $order = $orderItem->order;
        $oldOrderStatus = $order->status;

        DB::transaction(function () use ($orderItem, $order, $fulfilledQuantity, $actor) {

            // 1. Update the item fulfillment data
            $orderItem->update([
                'fulfilled_quantity' => $fulfilledQuantity,
            ]);

            // 2. Update & log order item status using dedicated service method
            $this->orderStatusService->updateOrderItemStatus($orderItem->refresh(), $actor);

            // 3. Update & log order status using dedicated service method
            $this->orderStatusService->updateOrderStatus($order->refresh());

            // 4. Handle refund if applicable
            $this->refundService->handleRefund($order->refresh());
        });

        // 6. Notify order updates if needed
        $this->orderCommunicationService->notifyOrderUpdates($order->refresh(), $oldOrderStatus);
    }

    private function generateReferenceId(): string
    {
        do {
            $reference = strtoupper(Str::random(16));
        } while ($this->codeRepository->getByReferenceId($reference));

        return $reference;
    }

    public function markInstantItemsProcessing(Order $order)
    {
        $items = $order->items->where('delivery_type', DeliveryType::INSTANT);

        foreach ($items as $orderItem) {
            $orderItem->update(['status' => OrderItemStatus::PROCESSING->value]);
            $this->statusUpdateLogService->store($orderItem, null, OrderItemStatus::PROCESSING->value);
        }
    }

    public function handleReturnedStock(OrderItem $orderItem)
    {
        $product = $this->productRepository->getByIdForUpdate($orderItem->product_id);

        // API products do not have local stock to return
        if ($product->source === ProductSource::API) {
            return;
        }

        $stockable = $orderItem->product_variant_value_id
            ? $this->productVariantValueRepository->getByIdForUpdate($orderItem->product_variant_value_id)
            : $product;

        if ($orderItem->delivery_type === DeliveryType::REQUIRES_CONFIRMATION) {
            // For confirmation items, we simply put the number back into manual_stock
            $stockable->increment('manual_stock', $orderItem->quantity);
        }

        if ($orderItem->delivery_type === DeliveryType::INSTANT) {
            // For instant items, we MUST restore the specific codes used in this order
            // so they become available for the next customer.
            $codeIds = $orderItem->codes()->pluck('code_id');

            $this->codeRepository->markAsUnused($codeIds);
        }
    }
}
