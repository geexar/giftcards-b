<?php

namespace App\Services\User;

use App\Enums\ProductSource;
use App\Enums\ProductStatus;
use App\Mail\ProductRestockedMail;
use App\Models\Product;
use App\Models\ProductVariantValue;
use App\Notifications\ProductRestockedNotification;
use App\Repositories\ProductAvailabilitySubscriptionRepostiory;
use App\Repositories\ProductRepository;
use App\Repositories\ProductVariantValueRepository;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductAvailabilitySubscriptionService
{
    public function __construct(
        private ProductAvailabilitySubscriptionRepostiory $subscriptionRepository,
        private ProductRepository $productRepository,
        private ProductVariantValueRepository $variantRepository
    ) {}

    public function subscribe(array $data)
    {
        $user = auth('user')->user();

        // Use authenticated user email if available, otherwise use request email
        $email = $user ? $user->email : $data['email'];
        $userId = $user ? $user->id : null;

        $id = $data['id'];
        $type = $data['type'];

        return $type === 'product'
            ? $this->subscribeToProduct($email, $userId, $id)
            : $this->subscribeToVariantValue($email, $userId, $id);
    }

    protected function subscribeToProduct(string $email, ?int $userId, string $productId)
    {
        $product = $this->productRepository->getById($productId);

        if (!$product || $product->status !== ProductStatus::ACTIVE) {
            throw new NotFoundHttpException('product not found');
        }

        $this->assertCategoryIsActive($product->category);

        if ($product->has_available_stock) {
            throw new BadRequestHttpException('product is already in stock');
        }

        $exists = $this->subscriptionRepository->exists([
            'user_id' => $userId,
            'email' => $email,
            'product_id' => $productId,
            'product_variant_value_id' => null,
        ]);

        if ($exists) {
            throw new BadRequestHttpException('you are already subscribed to this product');
        }

        return $this->subscriptionRepository->create([
            'user_id' => $userId,
            'email' => $email,
            'product_id' => $productId,
            'product_variant_value_id' => null,
        ]);
    }

    protected function subscribeToVariantValue(string $email, ?int $userId, string $variantValueId)
    {
        $variantValue = $this->variantRepository->getById($variantValueId);

        if (!$variantValue || !$variantValue->is_visible || $variantValue->product->status !== ProductStatus::ACTIVE) {
            throw new NotFoundHttpException('variant not found');
        }

        $this->assertCategoryIsActive($variantValue->product->category);

        if ($variantValue->has_available_stock) {
            throw new BadRequestHttpException('variant is already in stock');
        }

        $exists = $this->subscriptionRepository->exists([
            'user_id' => $userId,
            'email' => $email,
            'product_id' => $variantValue->product->id,
            'product_variant_value_id' => $variantValueId,
        ]);

        if ($exists) {
            throw new BadRequestHttpException('you are already subscribed to this variant');
        }

        return $this->subscriptionRepository->create([
            'user_id' => $userId,
            'email' => $email,
            'product_id' => $variantValue->product->id,
            'product_variant_value_id' => $variantValueId,
        ]);
    }

    private function assertCategoryIsActive($category)
    {
        while ($category) {
            if (!$category->is_active) {
                throw new BadRequestHttpException("belongs to inactive category");
            }
            $category = $category->parent;
        }
    }

    public function isUserAlreadySubscribedToProduct(string $productId, ?int $userId): bool
    {
        if (!$userId) return false;

        return $this->subscriptionRepository->exists([
            'user_id' => $userId,
            'product_id' => $productId,
            'product_variant_value_id' => null,
        ]);
    }

    public function isUserAlreadySubscribedToVariantValue(string $variantValueId, ?int $userId): bool
    {
        if (!$userId) return false;

        return $this->subscriptionRepository->exists([
            'user_id' => $userId,
            'product_variant_value_id' => $variantValueId,
        ]);
    }

    public function notifySubscribers(Product|ProductVariantValue $item)
    {
        $subscriptions = $item->availabilitySubscriptions;

        foreach ($subscriptions as $subscription) {
            if ($subscription->user_id) {
                Notification::send($subscription->user, new ProductRestockedNotification($subscription));
            } else {
                Mail::to($subscription->email)->send(new ProductRestockedMail($subscription));
            }
        }
    }

    public function doesProductHasAvailabilitySubscribtions(string $productId): bool
    {
        return $this->subscriptionRepository->exists([
            'product_id' => $productId,
            'product_variant_value_id' => null,
        ]);
    }

    public function doesVariantValueHasAvailabilitySubscribtions(string $variantValueId): bool
    {
        return $this->subscriptionRepository->exists([
            'product_variant_value_id' => $variantValueId
        ]);
    }
}
