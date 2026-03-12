<?php

namespace App\Services\User;

use App\Enums\ProductSource;
use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariantValue;
use App\Repositories\CartRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProductVariantValueRepository;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CartService
{
    public function __construct(
        private ProductRepository $productRepository,
        private ProductVariantValueRepository $productVariantValueRepository,
        private CartRepository $cartRepository
    ) {}

    /* -----------------------------------------------------------------
        CART FETCH
       -----------------------------------------------------------------*/
    public function getCart()
    {
        $user = auth('user')->user();

        if ($user) {
            return $this->cartRepository->getByUserId($user->id);
        }

        return $this->cartRepository->getByGuestToken($this->resolveGuestToken());
    }

    /* -----------------------------------------------------------------
        INCREASE ITEM QUANTITY
       -----------------------------------------------------------------*/
    public function increaseItemQuantity(string $type, string $id)
    {
        $cart = $this->getCart();

        $maxItemCount = getSetting('order_limits', 'max_units_per_order');

        if ($cart->quantity >= $maxItemCount) {
            throw new BadRequestHttpException(__("Can't add more than :max items to cart", ['max' => $maxItemCount]));
        }

        return $type === 'product'
            ? $this->increaseProductQuantity($cart, $id)
            : $this->increaseVariantQuantity($cart, $id);
    }

    protected function increaseProductQuantity(Cart $cart, string $productId)
    {
        $product = $this->productRepository->getById($productId);

        if (! $product || $product->status != ProductStatus::ACTIVE) {
            throw new NotFoundHttpException('product not found');
        }

        $this->assertCategoryIsActive($product->category);

        if ($product->has_variants) {
            throw new BadRequestHttpException("Can't add product with variants");
        }

        if ($product->source == ProductSource::LOCAL && ($product->marked_as_out_of_stock || !$product->has_available_stock)) {
            throw new BadRequestHttpException("product is out of stock");
        }

        if ($product->source == ProductSource::API && !$product->api_stock_available) {
            throw new BadRequestHttpException("product is out of stock");
        }

        $cartItem = $cart->items->where('product_id', $productId)->first();

        if ($cartItem) {
            $cartItem->increment('quantity');
            return;
        }

        $cart->items()->create([
            'product_id' => $productId,
            'quantity' => 1,
        ]);
    }

    protected function increaseVariantQuantity(Cart $cart, string $variantId)
    {
        $variantValue = $this->productVariantValueRepository->getById($variantId);

        if (
            ! $variantValue || ! $variantValue->is_visible ||
            $variantValue->product->status != ProductStatus::ACTIVE
        ) {
            throw new NotFoundHttpException("variant value not found");
        }

        $this->assertCategoryIsActive($variantValue->product->category);

        // Updated: Use 'stock' accessor
        if ($variantValue->marked_as_out_of_stock || !$variantValue->has_available_stock) {
            throw new BadRequestHttpException(__("variant value is out of stock"));
        }

        $cartItem = $cart->items->where('product_variant_value_id', $variantId)->first();

        if ($cartItem) {
            $cartItem->increment('quantity');
            return;
        }

        $cart->items()->create([
            'product_variant_value_id' => $variantId,
            'product_id' => $variantValue->product->id,
            'quantity' => 1,
        ]);
    }

    /* -----------------------------------------------------------------
        DECREASE ITEM QUANTITY
       -----------------------------------------------------------------*/
    public function decreaseItemQuantity(string $type, string $id)
    {
        $cart = $this->getCart();

        $cartItem = $type === 'product'
            ? $cart->items->where('product_id', $id)->first()
            : $cart->items->where('product_variant_value_id', $id)->first();

        if (! $cartItem) {
            throw new BadRequestHttpException('item not in the cart');
        }

        if ($cartItem->quantity > 1) {
            $cartItem->decrement('quantity');
            return;
        }

        $cartItem->delete();
    }

    /* -----------------------------------------------------------------
        REMOVE ITEM FROM CART COMPLETELY
       -----------------------------------------------------------------*/
    public function removeItemFromCart(string $type, string $id)
    {
        $cart = $this->getCart();

        $cartItem = $type === 'product'
            ? $cart->items->where('product_id', $id)->first()
            : $cart->items->where('product_variant_value_id', $id)->first();

        if (! $cartItem) {
            throw new BadRequestHttpException('item not in the cart');
        }

        $cartItem->delete();
    }

    /* -----------------------------------------------------------------
        HELPERS
       -----------------------------------------------------------------*/
    private function assertCategoryIsActive($category)
    {
        while ($category) {
            if (! $category->is_active) {
                throw new BadRequestHttpException("belongs to inactive category");
            }
            $category = $category->parent;
        }
    }

    private function resolveGuestToken()
    {
        $token = request()->header('X-Guest-Token');

        if (! $token) {
            throw new BadRequestHttpException('Must Provide Guest Token or Auth Token');
        }

        return $token;
    }

    public function preCheckoutValidation(): array
    {
        $cart = $this->getCart();
        $messages = [];

        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product;
            $variantValue = $cartItem->variantValue;

            $itemName = $product->name;
            if ($variantValue) {
                $itemName .= ' - ' . $variantValue->value;
            }

            /**
             * 1. GENERAL AVAILABILITY CHECKS
             */
            if ($product->status !== ProductStatus::ACTIVE) {
                $cartItem->delete();
                $messages[] = __('item_no_longer_available', ['item' => $itemName]);
                continue;
            }

            if (!$this->isCategoryTreeActive($product->category)) {
                $cartItem->delete();
                $messages[] = __('item_no_longer_available', ['item' => $itemName]);
                continue;
            }

            if ($variantValue && !$variantValue->is_visible) {
                $cartItem->delete();
                $messages[] = __('item_no_longer_available', ['item' => $itemName]);
                continue;
            }

            /**
             * 2. STOCK VALIDATION
             */
            if ($product->source === ProductSource::API) {
                if (!$this->isApiProductInStock($product)) {
                    $cartItem->delete();
                    $messages[] = __('item_removed_out_of_stock', ['item' => $itemName]);
                }
                continue;
            }

            $availableStock = $variantValue
                ? $this->resolveVariantValueAvailableStock($variantValue)
                : $this->resolveProductAvailableStock($product);

            if ($availableStock <= 0) {
                $cartItem->delete();
                $messages[] = __('item_removed_out_of_stock', ['item' => $itemName]);
                continue;
            }

            if ($cartItem->quantity > $availableStock) {
                $cartItem->update(['quantity' => $availableStock]);
                $messages[] = __('quantity_updated', ['available' => $availableStock, 'item' => $itemName]);
            }
        }

        return $messages;
    }

    private function isCategoryTreeActive($category): bool
    {
        while ($category) {
            if (!$category->is_active) {
                return false;
            }
            $category = $category->parent;
        }
        return true;
    }

    private function isApiProductInStock(Product $product): bool
    {
        return $product->api_stock_available;
    }

    private function resolveProductAvailableStock(Product $product): int
    {
        if ($product->marked_as_out_of_stock) {
            return 0;
        }

        return (int) $product->available_stock;
    }

    private function resolveVariantValueAvailableStock(ProductVariantValue $variantValue): int
    {
        if ($variantValue->marked_as_out_of_stock) {
            return 0;
        }

        return (int) $variantValue->available_stock;
    }

    public function clearCart(Cart $cart)
    {
        $cart->items()->delete();
    }
}
