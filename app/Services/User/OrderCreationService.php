<?php

namespace App\Services\User;

use App\Enums\MarkupFeeOrigin;
use App\Enums\OrderStatus;
use App\Enums\ProductSource;
use App\Models\Product;
use App\Models\ProductVariantValue;
use App\Repositories\OrderRepository;
use App\Repositories\OrderItemRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProductVariantValueRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class OrderCreationService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private OrderItemRepository $orderItemRepository,
        private CartService $cartService,
        private PaymentMethodRepository $paymentMethodRepository,
        private ProductRepository $productRepository,
        private ProductVariantValueRepository $productVariantValueRepository,
        private OrderStockReservationService $stockReservationService
    ) {}

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $userId = auth('user')->id() ?? null;
            $guestName = $userId ? null : $data['guest_name'];
            $guestEmail = $userId ? null : $data['guest_email'];
            $giftedEmail = $data['send_as_gift'] ? $data['gifted_email'] : null;

            $paymentMethod = $this->getPaymentMethod($data['payment_method']);

            $order = $this->orderRepository->create([
                'order_no' => $this->generateOrderNo(),
                'user_id' => $userId,
                'name' => $guestName,
                'email' => $guestEmail,
                'payment_method_id' => $paymentMethod->id,
                'is_gifted' => $data['send_as_gift'],
                'gifted_email' => $giftedEmail,
                'status' => OrderStatus::WAITING_PAYMENT
            ]);

            $cart = $this->cartService->getCart();

            foreach ($cart->items as $cartItem) {
                $item = $cartItem->item;
                // Calculate markup fee
                $markupFee = $this->getMarkupFee($item);

                // Calculate total
                $total = $item->user_facing_price * $cartItem->quantity;

                // Create order item
                $orderItem = $this->orderItemRepository->create([
                    'item_no' => $this->generateItemNo(),
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_variant_value_id' => $cartItem->product_variant_value_id,
                    'delivery_type' => $item->delivery_type->value,
                    'price' => $item->final_price,
                    'user_facing_price' => $item->user_facing_price,
                    'quantity' => $cartItem->quantity,
                    'markup_fee_origin' => $markupFee['origin'],
                    'markup_fee_type' => $markupFee['type'],
                    'markup_fee_value' => $markupFee['value'],
                    'total' => $total,
                ]);
            }

            // Reserve stock
            if ($cartItem->product->source == ProductSource::LOCAL) {
                $this->stockReservationService->reserveStock($orderItem);
            }

            // Update order total
            $order->update(['total' => $order->items->sum('total')]);

            return $order->refresh();
        });
    }

    private function getMarkupFee(Product|ProductVariantValue $item)
    {
        $product = $item instanceof Product ? $item : $item->product;
        
        return [
            'origin' => $product->has_custom_markup_fee ? MarkupFeeOrigin::CUSTOM : MarkupFeeOrigin::GLOBAL,
            'type'   => $product->has_custom_markup_fee ? $product->custom_markup_fee_type : getSetting('markup_fee', 'markup_fee_type'),
            'value'  => $product->has_custom_markup_fee ? $product->custom_markup_fee_value : getSetting('markup_fee', 'markup_fee_value'),
        ];
    }

    private function generateOrderNo(): string
    {
        do {
            $code = 'ORD-' . strtoupper(Str::random(8));
        } while ($this->orderRepository->getByOrderNo($code));

        return $code;
    }

    private function generateItemNo(): string
    {
        do {
            $code = 'ITM-' . strtoupper(Str::random(12));
        } while ($this->orderItemRepository->getByItemNo($code));

        return $code;
    }

    private function getPaymentMethod(string $paymentMethod)
    {
        return match ($paymentMethod) {
            'card' => $this->paymentMethodRepository->getStripe(),
            'wallet' => $this->paymentMethodRepository->getWallet(),
        };
    }
}
