<?php

namespace App\Services\Admin;

use App\Enums\DeliveryType;
use App\Enums\ProductNewImageStatus;
use App\Enums\ProductSource;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Repositories\CartItemRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ProductRepository;
use App\Services\ActivityLogService;
use App\Services\User\ProductAvailabilitySubscriptionService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductService
{
    public function __construct(
        private ProductRepository $productRepository,
        private DiscountService $discountService,
        private ActivityLogService $activityLogService,
        private CategoryRepository $categoryRepository,
        private CartItemRepository $cartItemRepository,
        private ProductAvailabilitySubscriptionService $productAvailabilitySubscriptionService
    ) {}

    public function getProduct(string $id)
    {
        $product = $this->productRepository->getById($id);

        if (!$product) {
            throw new NotFoundHttpException('product not found');
        }

        if ($product->source == ProductSource::API && !$product->viewed_by_admin) {
            $product->update(['viewed_by_admin' => true]);
        }

        return $product;
    }

    public function create(array $data): void
    {
        // Validate tags
        $this->validateTagsCount($data);

        // Validate category hierarchy
        $this->validateCategoryHierarchy($data['category_id']);

        // Create base product first
        DB::transaction(function () use ($data) {

            $product = $this->createBaseProduct($data);

            if (empty($data['has_variants'])) {
                $this->handleInvariantProduct($product, $data);
            } else {
                $this->handleVariantProduct($product, $data);
            }

            $this->activityLogService->store($product, 'product.created');
        });
    }

    public function validateCategoryHierarchy(string $categoryId): void
    {
        $category = $this->categoryRepository->getById($categoryId);

        // Check if category has children
        if ($category->childs()->exists()) {
            throw new BadRequestHttpException(__('The selected category has children.'));
        }
    }

    public function validateTagsCount(array $data, ?string $productId = null)
    {
        // Trending
        if (!empty($data['is_trending'])) {
            $trendingProductsCount = $this->productRepository->trendingCount($productId);
            $trendingCategoriesCount = $this->categoryRepository->trendingCount($productId);

            if ($trendingCategoriesCount + $trendingProductsCount >= 25) {
                throw new BadRequestHttpException(__('maximum number of trending items: 25'));
            }
        }

        // Popular
        if (!empty($data['is_popular'])) {
            $popularProductsCount = $this->productRepository->popularCount($productId);

            if ($popularProductsCount >= 25) {
                throw new BadRequestHttpException('maximum number of popular products: 25');
            }
        }

        // Featured
        if (!empty($data['is_featured'])) {
            $featuredProductsCount = $this->productRepository->featuredCount($productId);

            if ($featuredProductsCount >= 25) {
                throw new BadRequestHttpException('maximum number of featured products: 25');
            }
        }
    }

    /**
     * Create the base product data common for both invariant and variant products
     */
    private function createBaseProduct(array $data)
    {
        $baseData = [
            'source' => ProductSource::LOCAL,
            'name' => $data['name'],
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'],
            'is_best_seller' => $data['is_best_seller'] ?? 0,
            'is_popular' => $data['is_popular'] ?? 0,
            'is_featured' => $data['is_featured'] ?? 0,
            'is_trending' => $data['is_trending'] ?? 0,
            'is_global' => $data['is_global'],
            'has_custom_markup_fee' => $data['has_custom_markup_fee'],
            'custom_markup_fee_type' => $data['custom_markup_fee_type'] ?? null,
            'custom_markup_fee_value' => $data['custom_markup_fee_value'] ?? 0,
            'has_variants' => $data['has_variants'],
            'status' => $data['status'],
        ];

        $product = $this->productRepository->create($baseData);

        // Set countries if not global
        if (!$data['is_global']) {
            $product->countries()->sync($data['selected_countries'] ?? []);
        }

        // Handle image if provided
        if (isset($data['image'])) {
            $product->addMedia($data['image'])->toMediaCollection();
        }

        return $product;
    }

    private function handleInvariantProduct($product, array $data): void
    {
        // ----------------------------
        // 2. Calculate final price
        // ----------------------------
        $basePrice = $data['base_price'] ?? null;
        $finalPrice = $basePrice;

        if (!empty($data['has_discount']) && $finalPrice) {
            $discount = $this->discountService->calculateDiscount($basePrice, $data['discount_type'], $data['discount_value']);
            $finalPrice = $finalPrice - $discount;
        }

        // Manual stock if requires confirmation
        if ($data['delivery_type'] == DeliveryType::REQUIRES_CONFIRMATION->value) {
            $manualStock = $data['quantity'];
        } else {
            $manualStock = 0;
        }

        // ----------------------------
        // 3. Prepare data and update
        // ----------------------------
        $inVariantData = [
            'base_price' => $basePrice,
            'has_discount' => $data['has_discount'],
            'discount_type' => $data['discount_type'] ?? null,
            'discount_value' => $data['discount_value'] ?? 0,
            'final_price' => $finalPrice,
            'delivery_type' => $data['delivery_type'],
            'marked_as_out_of_stock' => $data['marked_as_out_of_stock'],
            'manual_stock' => $manualStock,
        ];

        $this->productRepository->update($product, $inVariantData);

        if ($data['delivery_type'] == DeliveryType::INSTANT->value) {
            foreach ($data['codes'] ?? [] as $codeData) {
                $product->codes()->create($codeData);
            }
        }
    }

    private function handleVariantProduct($product, array $data): void
    {
        // Create main variant
        $variant = $product->variant()->create([
            'name' => $data['variant_name'] ?? null,
        ]);

        foreach ($data['variant_values'] ?? [] as $variantData) {

            $basePrice = $variantData['base_price'] ?? null;
            $finalPrice = $basePrice;

            if (!empty($variantData['has_discount']) && $basePrice) {
                $discount = $this->discountService->calculateDiscount(
                    $basePrice,
                    $variantData['discount_type'],
                    $variantData['discount_value']
                );
                $finalPrice -= $discount;
            }

            // Manual stock if requires confirmation
            if ($variantData['delivery_type'] === DeliveryType::REQUIRES_CONFIRMATION->value) {
                $manualStock = $variantData['quantity'];
            } else {
                $manualStock = 0;
            }

            // Create the variant value
            $variantValue = $variant->values()->create([
                'description' => $variantData['description'] ?? null,
                'is_visible' => $variantData['is_visible'],
                'value' => $variantData['value'],
                'base_price' => $basePrice,
                'has_discount' => $variantData['has_discount'],
                'discount_type' => $variantData['discount_type'] ?? null,
                'discount_value' => $variantData['discount_value'] ?? 0,
                'final_price' => $finalPrice,
                'delivery_type' => $variantData['delivery_type'],
                'marked_as_out_of_stock' => $variantData['marked_as_out_of_stock'],
                'manual_stock' => $manualStock,
            ]);

            // For instant delivery, create codes
            if ($variantData['delivery_type'] == DeliveryType::INSTANT->value) {
                foreach ($variantData['codes'] ?? [] as $codeData) {
                    $variantValue->codes()->create($codeData);
                }
            }
        }
    }

    public function update(string $id, array $data): void
    {
        $product = $this->getProduct($id);

        // Validate tags
        $this->validateTagsCount($data, $id);

        // Validate category hierarchy
        $this->validateCategoryHierarchy($data['category_id']);

        // For API products, restrict certain fields from being updated
        if ($product->source == ProductSource::API) {
            $data['has_variants'] = 0;
            $data['category_id'] = $product->category_id;
            $data['base_price'] = $product->base_price;
        }

        DB::transaction(function () use ($product, $data) {

            // 1. Update base product fields
            $baseData = [
                'name' => $data['name'] ?? $product->name,
                'short_description' => $data['short_description'] ?? null,
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'],
                'is_best_seller' => $data['is_best_seller'] ?? 0,
                'is_popular' => $data['is_popular'] ?? 0,
                'is_featured' => $data['is_featured'] ?? 0,
                'is_trending' => $data['is_trending'] ?? 0,
                'is_global' => $data['is_global'],
                'has_custom_markup_fee' => $data['has_custom_markup_fee'],
                'custom_markup_fee_type' => $data['custom_markup_fee_type'] ?? null,
                'custom_markup_fee_value' => $data['custom_markup_fee_value'] ?? 0,
                'has_variants' => $data['has_variants'],
                'status' => $data['status'],
            ];

            $this->productRepository->update($product, $baseData);

            // 2. Sync countries
            if (array_key_exists('selected_countries', $data)) {
                $product->countries()->sync($data['is_global'] ? [] : $data['selected_countries']);
            }

            if (isset($data['image'])) {
                $product->clearMediaCollection();
                $product->addMedia($data['image'])->toMediaCollection();
            }

            // apply new API image if exists
            if (!empty($data['apply_new_api_image']) && $product->newAvailableImage) {
                // Remove current image
                $product->clearMediaCollection();

                // Copy the new available image
                $image = $product->newAvailableImage->image;
                $filePath = $image->getPath(); // full path to existing file
                $fileName = $image->file_name; // original file name

                $product->addMedia($filePath)
                    ->preservingOriginal()
                    ->usingFileName($fileName)
                    ->toMediaCollection();

                $product->newAvailableImage->update(['new_image_status' => ProductNewImageStatus::APPLIED]);
            }

            // 3. Cancel new API image
            if (!empty($data['cancel_new_api_image']) && $product->newAvailableImage) {
                $product->newAvailableImage->update(['new_image_status' => ProductNewImageStatus::CANCELED]);
            }

            // 4. Handle variants
            $hasVariants = (bool) ($data['has_variants']);
            if ($hasVariants) {
                $variant = $product->variant;
                if (!$variant) {
                    $variant = $product->variant()->create(['name' => $data['variant_name']]);
                } elseif (!empty($data['variant_name'])) {
                    $variant->update(['name' => $data['variant_name']]);
                }

                // Update variant values
                $this->updateVariantValues($variant, $data['variant_values'] ?? [], $data['variant_values_ids_to_remove'] ?? []);
            } else {
                // Handle invariant product
                $this->handleInvariantUpdate($product, $data);
            }

            $this->activityLogService->store($product, 'product.updated');
        });
    }

    private function handleInvariantUpdate($product, array $data): void
    {
        // capture stock status
        $wasInStock = $product->in_stock;

        $finalPrice = $data['base_price'] ?? null;

        if (!empty($data['has_discount']) && $finalPrice !== null) {
            $discount = $this->discountService->calculateDiscount($finalPrice, $data['discount_type'], $data['discount_value']);
            $finalPrice -= $discount;
        }

        $oldDeliveryType = $product->delivery_type;

        $updateData = [
            'base_price' => $data['base_price'],
            'has_discount' => $data['has_discount'],
            'discount_type' => $data['discount_type'] ?? null,
            'discount_value' => $data['discount_value'] ?? 0,
            'final_price' => $finalPrice,
            'delivery_type' => $data['delivery_type'] ?? null,
        ];

        // if delivery type changes from requires confirmation to instant, set manual stock to 0
        if ($data['delivery_type'] == DeliveryType::INSTANT->value && $oldDeliveryType == DeliveryType::REQUIRES_CONFIRMATION) {
            $updateData['manual_stock'] = 0;
        }

        // if delivery type changes from instant to requires confirmation, delete codes
        if ($data['delivery_type'] == DeliveryType::REQUIRES_CONFIRMATION->value && $oldDeliveryType == DeliveryType::INSTANT) {
            $product->codes()->delete();
        }

        // Only local products have stock logic
        if ($product->source == ProductSource::LOCAL) {
            $updateData['marked_as_out_of_stock'] = $data['marked_as_out_of_stock'];

            // Update codes first
            $this->updateCodes($product, $data['codes'] ?? [], $data['codes_ids_to_remove'] ?? []);

            // Handle Manual Stock
            // If delivery requires confirmation, we use the input quantity as manual_stock.
            // If it is Instant, manual_stock is null (stock is derived from codes count).
            if ($data['delivery_type'] == DeliveryType::REQUIRES_CONFIRMATION->value) {
                $updateData['manual_stock'] = $data['quantity'];
            } else {
                $updateData['manual_stock'] = 0;
            }
        }

        $this->productRepository->update($product, $updateData);

        if ($product->in_stock && !$wasInStock) {
            $this->productAvailabilitySubscriptionService->notifySubscribers($product);
        }
    }

    private function updateCodes($codeable, array $codes, array $idsToRemove): void
    {
        // 1. Update or create codes
        foreach ($codes as $codeData) {
            if (!empty($codeData['id'])) {
                $code = $codeable->codes()->where('codes.id', $codeData['id'])->first();

                if (!$code) {
                    throw new NotFoundHttpException("code with id {$codeData['id']} doesnt belong to this product or variant value");
                }

                if ($code->is_used) {
                    throw new BadRequestHttpException(__("code :code is already used", ['code' => $code->code]));
                }

                $code->update($codeData);
            } else {
                $codeable->codes()->create($codeData);
            }
        }

        // 2. Soft delete removed codes
        if (!empty($idsToRemove)) {
            $codes = $codeable->codes()->whereIn('codes.id', $idsToRemove)->get();

            foreach ($codes as $code) {
                $code->delete();
            }
        }
    }

    private function updateVariantValues($variant, array $values, array $idsToRemove): void
    {
        // 1. First, delete removed variant values before updating
        if (!empty($idsToRemove)) {
            $variantValues = $variant->values()->whereIn('id', $idsToRemove)->get();

            foreach ($variantValues as $variantValue) {
                $variantValue->delete();
                $variantValue->update(['value' => getInvalidatedValue($variantValue->value)]);
                $this->cartItemRepository->deleteItemsWithVariantValueId($variantValue->id);
            }
        }

        // 2. Then update or create the remaining variant values
        foreach ($values as $valueData) {
            $basePrice = $valueData['base_price'] ?? null;
            $finalPrice = $basePrice;

            if (!empty($valueData['has_discount']) && $basePrice !== null) {
                $discount = $this->discountService->calculateDiscount(
                    $basePrice,
                    $valueData['discount_type'],
                    $valueData['discount_value']
                );
                $finalPrice -= $discount;
            }

            $deliveryType = $valueData['delivery_type'] ?? null;

            if (!empty($valueData['id'])) {
                $variantValue = $variant->values()->where('product_variant_values.id', $valueData['id'])->first();

                if (!$variantValue) {
                    throw new NotFoundHttpException("variant value id: {$valueData['id']} not found for this product");
                }

                // Capture old delivery type and stock status
                $oldDeliveryType = $variantValue->delivery_type;
                $wasInStock = $variantValue->in_stock;

                $manualStock = $deliveryType === DeliveryType::REQUIRES_CONFIRMATION->value
                    ? $valueData['quantity']
                    : 0;

                $updateData = [
                    'description' => $valueData['description'] ?? null,
                    'is_visible' => $valueData['is_visible'],
                    'value' => $valueData['value'],
                    'base_price' => $basePrice,
                    'has_discount' => $valueData['has_discount'],
                    'discount_type' => $valueData['discount_type'] ?? null,
                    'discount_value' => $valueData['discount_value'] ?? 0,
                    'final_price' => $finalPrice,
                    'delivery_type' => $deliveryType,
                    'marked_as_out_of_stock' => $valueData['marked_as_out_of_stock'],
                    'manual_stock' => $manualStock,
                ];

                // --- Delivery type change handling ---
                if ($deliveryType == DeliveryType::INSTANT->value && $oldDeliveryType == DeliveryType::REQUIRES_CONFIRMATION) {
                    $updateData['manual_stock'] = 0;
                }

                if ($deliveryType == DeliveryType::REQUIRES_CONFIRMATION->value && $oldDeliveryType == DeliveryType::INSTANT) {
                    $variantValue->codes()->delete();
                }
                // --- End delivery type change handling ---

                $variantValue->update($updateData);
            } else {
                $manualStock = $deliveryType === DeliveryType::REQUIRES_CONFIRMATION->value
                    ? $valueData['quantity']
                    : 0;

                $variantValue = $variant->values()->create([
                    'description' => $valueData['description'] ?? null,
                    'is_visible' => $valueData['is_visible'],
                    'value' => $valueData['value'],
                    'base_price' => $basePrice,
                    'has_discount' => $valueData['has_discount'],
                    'discount_type' => $valueData['discount_type'] ?? null,
                    'discount_value' => $valueData['discount_value'] ?? 0,
                    'final_price' => $finalPrice,
                    'delivery_type' => $deliveryType,
                    'marked_as_out_of_stock' => $valueData['marked_as_out_of_stock'],
                    'manual_stock' => $manualStock,
                ]);
            }

            // Update codes
            $this->updateCodes(
                $variantValue,
                $valueData['codes'] ?? [],
                $valueData['codes_ids_to_remove'] ?? []
            );

            if (!empty($valueData['id']) && $variantValue->in_stock && !$wasInStock) {
                $this->productAvailabilitySubscriptionService->notifySubscribers($variantValue);
            }
        }
    }


    public function delete(string $id)
    {
        $product = $this->getProduct($id);

        if ($product->source == ProductSource::API) {
            throw new BadRequestHttpException("Cannot delete product imported from API.");
        }

        DB::transaction(function () use ($product) {
            // Delete product codes
            $product->codes()->delete();

            // Delete variant and its values + their codes
            if ($product->variant) {
                $product->variant->values->each(function ($variantValue) {
                    // Delete codes of this variant value
                    $variantValue->codes()->delete();
                    // Delete the variant value itself
                    $variantValue->delete();
                });

                // Delete the variant itself
                $product->variant->delete();
            }

            // Finally delete the product
            $product->delete();

            // Delete cart items with this product
            $this->cartItemRepository->deleteItemsWithProductId($product->id);

            $this->activityLogService->store($product, 'product.deleted');
        });
    }

    public function bulkStatusUpdate(array $data)
    {
        DB::transaction(function () use ($data) {
            foreach ($data['ids'] as $id) {
                $product = $this->productRepository->getById($id);

                if ($product->status == ProductStatus::DRAFTED) {
                    throw new BadRequestHttpException("can't update drafted product with id: {$id}");
                }

                $product->update(['status' => $data['status']]);

                $this->activityLogService->store($product, 'product.status_updated');
            }
        });
    }

    public function calculateProductQuantity(Product $product): int
    {
        if ($product->source === ProductSource::API) {
            return 0;
        }

        if (!$product->has_variants) {
            return $product->delivery_type === DeliveryType::INSTANT->value
                ? $product->validCodes()->count()
                : ($product->manual_stock ?? 0);
        }

        // Variant product
        return $product->variant->values->sum(function ($v) {
            return $v->delivery_type === DeliveryType::INSTANT->value
                ? $v->validCodes()->count()
                : ($v->manual_stock ?? 0);
        });
    }
}
