<?php

namespace App\Services\Admin;

use App\Enums\DeliveryType;
use App\Enums\ProductSource;
use App\Enums\StockStatus;
use App\Repositories\ProductRepository;
use App\Services\ActivityLogService;
use App\Services\Admin\ProductService;
use App\Services\User\ProductAvailabilitySubscriptionService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductInventoryService
{
    public function __construct(
        private ProductService $productService,
        private ProductRepository $productRepository,
        private ActivityLogService $activityLogService,
        private ProductAvailabilitySubscriptionService $productAvailabilitySubscriptionService
    ) {}

    public function getProduct(string $id)
    {
        $product = $this->productRepository->getById($id);

        if (!$product) {
            throw new NotFoundHttpException('product not found');
        }

        if ($product->source == ProductSource::API) {
            throw new BadRequestHttpException("api products don't have stock");
        }

        return $product;
    }

    public function clearProductStock(string $id)
    {
        $product = $this->getProduct($id);

        DB::transaction(function () use ($product) {
            if ($product->has_variants) {
                $product->variant->values->each(function ($variantValue) {
                    if ($variantValue->delivery_type === DeliveryType::REQUIRES_CONFIRMATION) {
                        $variantValue->update(['manual_stock' => 0]);
                    } else {
                        $variantValue->validCodes()->delete();
                        $variantValue->expiredCodes()->delete();
                    }
                });
            } else {
                if ($product->delivery_type === DeliveryType::REQUIRES_CONFIRMATION) {
                    $product->update(['manual_stock' => 0]);
                } else {
                    $product->validCodes()->delete();
                    $product->expiredCodes()->delete();
                }
            }

            // total stock is now dynamically computed, so no quantity column update needed
            $this->activityLogService->store($product, 'product.stock_cleared');
        });
    }

    public function restockProduct(string $id, array $data): void
    {
        $product = $this->getProduct($id);

        DB::transaction(function () use ($product, $data) {

            // ------------------------------------------------
            // INVARIANT PRODUCT
            // ------------------------------------------------
            if (!$product->has_variants) {

                $wasInStock = $product->in_stock;

                if ($product->delivery_type === DeliveryType::INSTANT) {
                    // Add codes
                    foreach ($data['codes'] as $codeData) {
                        $product->codes()->create($codeData);
                    }
                } else {
                    // REQUIRES_CONFIRMATION → add to manual_stock
                    $product->increment('manual_stock', (int) ($data['quantity']));
                }

                if ($product->in_stock && !$wasInStock) {
                    $this->productAvailabilitySubscriptionService->notifySubscribers($product);
                }
            }

            // ------------------------------------------------
            // VARIANT PRODUCT
            // ------------------------------------------------
            else {
                foreach ($data['variant_values'] as $variantData) {

                    $variantValue = $product->variant
                        ->values()
                        ->where('product_variant_values.id', $variantData['id'])
                        ->first();

                    if (!$variantValue) {
                        throw new NotFoundHttpException(
                            "variant value id {$variantData['id']} not found for this product"
                        );
                    }

                    // capture stock status
                    $wasInStock = $variantValue->in_stock;

                    if ($variantValue->delivery_type === DeliveryType::INSTANT) {
                        foreach ($variantData['codes'] as $codeData) {
                            $variantValue->codes()->create($codeData);
                        }
                    } else {
                        $variantValue->increment('manual_stock', (int) ($variantData['quantity']));
                    }

                    // Notify stock availability subscribers
                    if ($variantValue->in_stock && !$wasInStock) {
                        $this->productAvailabilitySubscriptionService->notifySubscribers($variantValue);
                    }
                }
            }

            // ------------------------------------------------
            // FINAL ACTIVITY LOG (ONCE)
            // ------------------------------------------------
            $this->activityLogService->store($product, 'product.restocked');
        });
    }

    public function getStockStatus(int $stock)
    {
        $threshold = getSetting('inventory', 'stock_threshold');

        if ($stock == 0) {
            return StockStatus::OUT_OF_STOCK->value;
        }

        if ($stock < $threshold) {
            return StockStatus::LOW->value;
        }

        return StockStatus::NORMAL->value;
    }
}
