<?php

namespace App\Services\User;

use App\Dto\OrderResponse;
use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductSource;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Repositories\AdminRepository;
use App\Repositories\CartRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProductVariantValueRepository;
use App\Repositories\RatingRepository;
use App\Repositories\TransactionRepository;
use App\Services\Admin\StatusUpdateLogService;
use App\Services\Admin\TransactionService;
use App\Services\Stripe\StripeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderItemRepository $orderItemRepository,
        private OrderCreationService $orderCreationService,
        private OrderFulfillmentService $orderFulfillmentService,
        private TransactionRepository $transactionRepository,
        private StripeService $stripeService,
        private ProfitCaclulationService $profitCaclulationService,
        private RatingRepository $ratingRepository,
        private ProductRepository $productRepository,
        private ProductVariantValueRepository $productVariantValueRepository,
        private StatusUpdateLogService $statusUpdateLogService,
        private OrderStatusService $orderStatusService,
        private RefundService $refundService,
        private CartService $cartService,
        private CartRepository $cartRepository,
        private OrderCommunicationService $orderCommunicationService,
        private TransactionService $transactionService,
        private AdminRepository $adminRepository,
        private ProductStockService $productStockService
    ) {}

    public function getOrder(string $orderNo)
    {
        $order = $this->orderRepository->getByOrderNo($orderNo);

        // Ensure the order exists and belongs to the authenticated user
        if (!$order || $order->user_id !== auth('user')->id() || $order->status == OrderStatus::WAITING_PAYMENT) {
            throw new NotFoundHttpException('order not found');
        }

        return $order;
    }

    public function createOrder(array $data)
    {
        $this->checkoutValidation($data);

        return match ($data['payment_method']) {
            'wallet' => $this->placeWalletOrder($data),
            'card'   => $this->placeStripeOrder($data),
        };
    }

    public function placeWalletOrder(array $data)
    {
        // Create order
        $order = $this->orderCreationService->create($data);

        // Process payment and fulfill the order
        $this->processWalletPaidOrder($order);

        // Return response with order details and message
        return new OrderResponse(
            order: $order,
            message: $this->getResponseMessage($order)
        );
    }

    public function placeStripeOrder(array $data)
    {
        // Create order
        $order = $this->orderCreationService->create($data);

        // get cart
        $cart = $this->cartService->getCart();

        $frontendUrl = config('app.frontend_url');

        // Generate Stripe payment URL
        $paymentUrl = $this->stripeService->createCheckoutPaymentUrl(
            order: $order,
            cartId: $cart->id,
            successUrl: "$frontendUrl/success-checkout/$order->order_no",
            cancelUrl: "$frontendUrl/checkout"
        );

        // Return response with order details and payment URL
        return new OrderResponse(
            order: $order,
            payment_url: $paymentUrl
        );
    }

    public function processStripePaidOrder(Order $order, string $referenceId, string $cartId)
    {
        DB::transaction(function () use ($order, $referenceId, $cartId) {

            // Fulfill the order items
            $this->orderFulfillmentService->process($order);

            // Record Stripe transaction
            $this->createStripeOrderTransaction($order, $referenceId);

            // Clear the cart
            $cart = $this->cartRepository->getById($cartId);
            $this->cartService->clearCart($cart);

            // Send order confirmation email
            $this->sendOrderMail($order);

            // Check for low stock
            $this->checkOrderItemsStock($order);
        });
    }

    public function processWalletPaidOrder(Order $order)
    {
        DB::transaction(function () use ($order) {

            // Sync user's wallet balance
            $userBalance = $this->transactionRepository->getUserBalance($order->user_id);
            $order->user->update(['balance' => $userBalance]);

            // Fulfill the order items
            $this->orderFulfillmentService->process($order);

            // Record wallet transaction for this order
            $this->createWalletOrderTransaction($order);

            // Clear the cart
            $cart = $this->cartService->getCart();
            $this->cartService->clearCart($cart);

            // Send order confirmation email
            $this->sendOrderMail($order);

            // Check for low stock
            $this->checkOrderItemsStock($order);
        });
    }

    public function createWalletOrderTransaction(Order $order)
    {
        // Create a wallet transaction that deducts the order amount from user balance
        return $this->transactionRepository->create([
            'transaction_no' => $this->transactionService->generateTransactionNo(),
            'user_id'           => $order->user_id,
            'order_id'          => $order->id,
            'type'              => TransactionType::PURCHASE,
            'actor_type'        => User::class,
            'actor_id'          => $order->user_id,
            'amount'            => $order->total * -1,
            'status'            => TransactionStatus::SUCCESS,
            'payment_method_id' => 1,
            'affects_wallet'    => true,
            'projected_profit' => $this->profitCaclulationService->getProjectedProfit($order),
            'actual_profit' => $this->profitCaclulationService->getActualProfit($order),
        ]);
    }

    public function createStripeOrderTransaction(Order $order, string $referenceId)
    {
        // Create a Stripe transaction record
        return $this->transactionRepository->create([
            'transaction_no' => $this->transactionService->generateTransactionNo(),
            'user_id'           => $order->user_id,
            'order_id'          => $order->id,
            'type'              => TransactionType::PURCHASE,
            'actor_type'        => User::class,
            'actor_id'          => $order->user_id,
            'amount'            => $order->total,
            'reference_id'      => $referenceId,
            'status'            => TransactionStatus::SUCCESS,
            'payment_method_id' => 2,
            'affects_wallet'    => false,
            'projected_profit' => $this->profitCaclulationService->getProjectedProfit($order),
            'actual_profit' => $this->profitCaclulationService->getActualProfit($order),
        ]);
    }

    public function cancelOrder(string $orderNo)
    {
        $order = $this->getOrder($orderNo);

        if (!$order->can_cancel) {
            throw new BadRequestHttpException('order cannot be canceled');
        }

        // Capture old order status
        $oldOrderStatus = $order->status;

        DB::transaction(function () use ($order) {
            foreach ($order->items as $orderItem) {
                $this->cancelItem($orderItem);
            }

            $this->orderStatusService->updateOrderStatus($order);
        });

        // Notify order updates if needed
        $this->orderCommunicationService->notifyOrderUpdates($order->refresh(), $oldOrderStatus);
    }

    public function getOrderItem(string $orderItemNo)
    {
        $orderItem = $this->orderItemRepository->getByItemNo($orderItemNo);

        if (!$orderItem || $orderItem->order->user_id !== auth('user')->id() || $orderItem->order->status == OrderStatus::WAITING_PAYMENT) {
            throw new NotFoundHttpException('order item not found');
        }

        return $orderItem;
    }

    public function cancelOrderItem(string $orderItemNo)
    {
        $orderItem = $this->getOrderItem($orderItemNo);

        if (!$orderItem->can_cancel) {
            throw new BadRequestHttpException('order item cannot be canceled');
        }

        DB::transaction(function () use ($orderItem) {
            $this->cancelItem($orderItem);
        });
    }

    public function cancelItem(OrderItem $orderItem)
    {
        $orderItem->update([
            'status' => OrderItemStatus::CANCELED,
            'fulfilled_quantity' => 0
        ]);

        // Update and log order item status
        $this->statusUpdateLogService->store($orderItem, OrderItemStatus::PENDING_CONFIRMATION->value, OrderItemStatus::CANCELED->value);

        // Update and log order status
        $this->orderStatusService->updateOrderStatus($orderItem->order->refresh());

        // handle quantity
        $this->orderFulfillmentService->handleReturnedStock($orderItem);

        // Handle refund if applicable
        $this->refundService->handleRefund($orderItem->order->refresh());
    }

    public function rateOrder(string $orderNo, array $data): void
    {
        // Fetch order by order number
        $order = $this->getOrder($orderNo);

        // Ensure order is allowed to be rated (business rule)
        if (! $order->can_rate) {
            throw new BadRequestHttpException('order cannot be rated');
        }

        $ratableItems = $order->items->whereIn('status', [OrderItemStatus::COMPLETED, OrderItemStatus::PARTIALLY_FULFILLED]);

        if (count($ratableItems) > count($data['items'])) {
            throw new BadRequestHttpException('must rate all order items');
        }

        DB::transaction(function () use ($order, $data) {

            $ratedProductIds = [];
            $ratedVariantValueIds = [];

            /**
             * 1. Create ratings per order item
             */
            foreach ($data['items'] as $ratingData) {

                // Fetch order item by item number
                $orderItem = $this->getOrderItem($ratingData['item_no']);

                // Prevent rating items that do not belong to the order
                if ($orderItem->order_id != $order->id) {
                    throw new BadRequestHttpException('invalid order item');
                }

                if (!in_array($orderItem->status, [OrderItemStatus::COMPLETED, OrderItemStatus::PARTIALLY_FULFILLED], true)) {
                    throw new BadRequestHttpException("item must be completed or partially fulfilled");
                }

                // Create item-level rating
                $this->ratingRepository->create([
                    'user_id'       => $order->user_id,
                    'order_id'      => $order->id,
                    'order_item_id' => $orderItem->id,
                    'rating'        => $ratingData['rating'],
                ]);

                // Track product or variant IDs
                if ($orderItem->product_variant_value_id) {
                    $ratedVariantValueIds[] = $orderItem->product_variant_value_id;
                } else {
                    $ratedProductIds[] = $orderItem->product_id;
                }
            }

            /**
             * 2. Create overall order rating (average of item ratings)
             */
            $orderAvgRating = $order->ratings()
                ->whereNotNull('order_item_id')
                ->avg('rating');

            $this->ratingRepository->create([
                'user_id'  => $order->user_id,
                'order_id' => $order->id,
                'rating'   => $orderAvgRating,
                'comment'  => $data['comment'] ?? null,
            ]);

            /**
             * 3. Recalculate and persist average rating per affected product
             */

            // Update average rating for products
            foreach (array_unique($ratedProductIds) as $productId) {
                $avgProductRating = $this->ratingRepository->getAvgRatingForProduct($productId);
                $product = $this->productRepository->getById($productId);
                $product->update(['avg_rating' => $avgProductRating]);
            }

            // Update average rating for variants
            foreach (array_unique($ratedVariantValueIds) as $variantValueId) {
                $avgVariantRating = $this->ratingRepository->getAvgRatingForVariantValue($variantValueId);
                $variantValue = $this->productVariantValueRepository->getById($variantValueId);
                $variantValue->update(['avg_rating' => $avgVariantRating]);
            }
        });
    }

    public function sendOrderMail(Order $order) {}

    public function getResponseMessage(Order $order)
    {
        // Return message depending on order item statuses
        if ($this->hasItemWithStatus($order, OrderItemStatus::PENDING_CONFIRMATION)) {
            return __("Order placed successfully. Please wait until your order is confirmed.");
        }

        if ($this->hasApiItem($order)) {
            return __("Order placed successfully. Your order is processing now.");
        }

        return __("Your order was completed successfully with the available quantity.");
    }

    public function hasItemWithStatus(Order $order, OrderItemStatus $orderItemStatus)
    {
        foreach ($order->items as $item) {
            if ($item->status == $orderItemStatus) {
                return true;
            }
        }

        return false;
    }

    public function hasApiItem(Order $order)
    {
        foreach ($order->items as $item) {
            if ($item->product->source == ProductSource::API) {
                return true;
            }
        }

        return false;
    }

    public function checkoutValidation(array $data)
    {
        $cart = $this->cartService->getCart();

        // 1. Check if empty first (Avoids unnecessary processing)
        if ($cart->items->isEmpty()) {
            throw new BadRequestHttpException(__('cart is empty'));
        }

        // 2. Run deep validation (stock, status, categories)
        // This might delete items or change quantities
        $validationMessages = $this->cartService->preCheckoutValidation();

        // 3. If items were removed/changed during step 2, alert the user
        if (!empty($validationMessages)) {
            throw ValidationException::withMessages([
                'cart' => $validationMessages,
            ]);
        }

        // 4. Wallet Balance Check (Must happen after cart is validated so 'total' is accurate)
        if ($data['payment_method'] === 'wallet') {
            $user = auth('user')->user();

            if (!$user) {
                throw new BadRequestHttpException('Registered users only can pay with wallet');
            }

            if ($user->balance < $cart->total) {
                throw ValidationException::withMessages([
                    'balance' => [__('insufficient balance')],
                ]);
            }
        }
    }

    public function orderStatusInfo(string $orderNo)
    {
        $order = $this->orderRepository->getByOrderNo($orderNo);

        return [
            'order_no' => $order->order_no,
            'status'    => $order->status->value,
            'gifted_email'   => $order->gifted_email
        ];
    }

    public function checkOrderItemsStock(Order $order): void
    {
        $lowStockThreshold = getSetting('inventory', 'stock_threshold');
        $admins = $this->adminRepository->getNotifiedAdmins('update product stock');

        foreach ($order->items as $item) {

            // Skip API products
            if ($item->product->source == ProductSource::API) {
                continue;
            }

            // Check stock for local products
            $totalProductStock = $this->productStockService->getLocalProductTotalStock($item->product);

            if ($totalProductStock <= $lowStockThreshold) {
                Notification::send($admins, new LowStockNotification($item->product));
            }
        }
    }
}
