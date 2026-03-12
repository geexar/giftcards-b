<?php

namespace App\Services\Admin;

use App\Enums\ProductNewImageStatus;
use App\Enums\ProductSource;
use App\Enums\ProductStatus;
use App\Enums\ProductSyncLogItemStatus;
use App\Enums\ProductSyncStatus;
use App\Enums\ProductSyncType;
use App\Jobs\SyncApiCategories;
use App\Jobs\SyncApiProduct;
use App\Jobs\SyncApiProducts;
use App\Jobs\SyncRemovedApiProduct;
use App\Models\Admin;
use App\Models\Category;
use App\Models\ProductSyncLog;
use App\Notifications\ProductSyncNotification;
use App\Repositories\AdminRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\IntegrationRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProductSyncLogRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProductSyncService
{
    public function __construct(
        private ProductRepository $productRepository,
        private CategoryRepository $categoryRepository,
        private ProductSyncLogRepository $productSyncLogRepository,
        private IntegrationRepository $integrationRepository,
        private DiscountService $discountService,
        private AdminRepository $adminRepository
    ) {}

    public function startSync(?Admin $admin = null)
    {
        // Prevent running multiple syncs at the same time
        $inProgressSync = $this->productSyncLogRepository->inProgressSync();
        if ($inProgressSync) {
            throw new BadRequestHttpException('Sync already in progress');
        }

        // Determine sync type
        $type = $admin ? ProductSyncType::MANUAL : ProductSyncType::AUTOMATIC;
        $admin_id = $admin?->id;

        // Create sync log immediately
        $syncLog = $this->productSyncLogRepository->create([
            'type'      => $type,
            'admin_id'  => $admin_id,
            'status'    => ProductSyncStatus::IN_PROGRESS,
        ]);

        // Dispatch orchestrator job
        SyncApiProducts::dispatch($syncLog);
    }

    /**
     * Dispatch removed, added, or updated product jobs
     */
    public function dispatchProductJobs(ProductSyncLog $productSyncLog): void
    {
        try {
            $apiProducts = $this->loadApiProducts();
            $internalProducts = $this->productRepository->getApiProducts();
            $categories = $this->categoryRepository->getApiCategories();

            $totalJobs = 0;

            // Removed products
            $removedProducts = $internalProducts->filter(
                fn($product) => !$apiProducts->pluck('productId')->contains($product->external_id)
            );
            foreach ($removedProducts as $removed) {
                SyncRemovedApiProduct::dispatch($removed, $productSyncLog);
                $totalJobs++;
            }

            // Added / updated products
            foreach ($apiProducts as $data) {
                $product = $internalProducts->firstWhere('external_id', $data['productId']);
                $category = $categories->firstWhere('external_id', $data['categoryId']);

                if (!$category) {
                    continue;
                }

                SyncApiProduct::dispatch($data, $product, $category, $productSyncLog);
                $totalJobs++;
            }

            // Update sync log total jobs
            $productSyncLog->update([
                'total_jobs' => $totalJobs,
                'completed_jobs' => 0,
            ]);
        } catch (\Throwable $e) {
            // Log any product dispatch errors but don't throw
            $productSyncLog->update(['status' => ProductSyncStatus::FAILED]);

            Log::error('Product sync orchestration failed', [
                'sync_log_id' => $productSyncLog->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Increment job counter and check if sync is complete.
     */
    public function incrementSyncJobCounter(ProductSyncLog $syncLog): void
    {
        $syncLog->increment('completed_jobs');
        $syncLog->refresh();

        if ($syncLog->completed_jobs >= $syncLog->total_jobs) {
            $syncLog->update(['status' => ProductSyncStatus::SUCCESS]);

            // Notify admins
            $notifiedAdmins = $this->adminRepository->getNotifiedAdmins('show products');
            $notification = new ProductSyncNotification($syncLog);
            Notification::send($notifiedAdmins, $notification);
        }
    }

    /**
     * Load API products from JSON files (English + Arabic) and merge the names.
     */
    public function loadApiProducts(): Collection
    {
        $fileEn = storage_path('app/private/products_en.json');
        $fileAr = storage_path('app/private/products_ar.json');

        $productsEn = json_decode(file_get_contents($fileEn), true);
        $productsAr = json_decode(file_get_contents($fileAr), true);

        return collect($productsEn)
            ->map(function ($productEn, $index) use ($productsAr) {
                $productAr = $productsAr[$index];

                // Merge product names into a single array with 'en' and 'ar'
                $productEn['productName'] = [
                    'en' => $productEn['productName'],
                    'ar' => $productAr['productName'],
                ];

                return $productEn;
            })
            ->filter(fn($product) => $product['available']); // filter only available products
    }

    /**
     * Handle removing a product from DB and creating a sync log item.
     */
    public function handleRemovedProduct($product, $productSyncLog): void
    {
        $product->delete();

        $productSyncLog->items()->create([
            'product_id' => $product->id,
            'changes' => null,
            'status' => ProductSyncLogItemStatus::REMOVED,
            'new_image_available' => false,
        ]);
    }

    /**
     * Handle adding a new product and attaching image.
     */
    public function handleAddedProduct(array $data, Category $category, $productSyncLog): void
    {
        $basePriceField = $this->getBasePriceSourceField();

        $basePrice = $data[$basePriceField];

        $product = $this->productRepository->create([
            'source'      => ProductSource::API,
            'status'      => ProductStatus::DRAFTED,
            'external_id' => $data['productId'],
            'category_id' => $category->id,
            'provider_original_price' => $data['productPrice'],
            'base_price'  => $basePrice,
            'final_price' => $basePrice,
            'name'        => $data['productName'],
            'viewed_by_admin' => false,
            'api_stock_available' => $data['available'],
            'api_stock_last_checked_at' => Carbon::now(),
        ]);

        $productSyncLog->items()->create([
            'product_id' => $product->id,
            'changes' => null,
            'status' => ProductSyncLogItemStatus::ADDED,
            'new_image_available' => false,
        ]);

        // Attach image if exists
        if (!empty($data['productImage'])) {
            $product->addMediaFromUrl($data['productImage'])->toMediaCollection();
        }
    }

    /**
     * Handle updating existing product and logging changes.
     */
    public function handleUpdatedProduct($product, array $data, $productSyncLog): void
    {
        $basePriceField = $this->getBasePriceSourceField();

        $changes = [];

        // Detect base price change
        if ($product->base_price != $data[$basePriceField]) {
            $changes[] = [
                'column' => 'base_price',
                'old_value' => $product->base_price,
                'new_value' => $data[$basePriceField],
            ];

            $product->provider_original_price = $data['productPrice'];
            $product->base_price = $data[$basePriceField];

            if ($product->has_discount) {
                $discount = $this->discountService->calculateDiscount($product->base_price, $product->discount_type, $product->discount_value);
                $product->final_price = $product->base_price - $discount;
            }
        }

        $currentEn = $product->getTranslation('name', 'en');
        $incomingEn = $data['productName']['en'];

        // Check only English for change
        if ($currentEn !== $incomingEn) {
            $changes[] = [
                'column' => 'name',
                'old_value' => $product->getTranslation('name', 'en'),
                'new_value' => $data['productName']['en'],
            ];

            // Update full translations
            $product->setTranslations('name', $data['productName']);
        }

        // Check if image has changed by filename
        $imageChanged = false;
        if (!empty($data['productImage'])) {
            if ($this->imageChanged($product->image, $data['productImage'])) {
                $imageChanged = true;
            }
        }

        // Nothing changed → skip
        if (empty($changes) && !$imageChanged) {
            return;
        }

        // Save product updates
        $product->save();

        // cancel all pending new image sync log items for this product
        if ($imageChanged) {
            $product->syncLogs()->where('new_image_status', ProductNewImageStatus::PENDING->value)->update([
                'new_image_status' => ProductNewImageStatus::CANCELED->value,
            ]);
        }

        // Create sync log
        $syncLogItem = $productSyncLog->items()->create([
            'product_id' => $product->id,
            'changes' => $changes ?: null,
            'status' => ProductSyncLogItemStatus::UPDATED,
            'new_image_available' => $imageChanged,
            'new_image_status' => ProductNewImageStatus::PENDING,
        ]);

        // Attach new image if changed
        if ($imageChanged) {
            $syncLogItem->addMediaFromUrl($data['productImage'])->toMediaCollection();
        }
    }

    /**
     * Check if product image has changed by comparing filename only.
     */
    private function imageChanged(Media $currentImage, string $newImage): bool
    {
        return $currentImage->file_name != basename($newImage);
    }

    private function getBasePriceSourceField()
    {
        $giftCardSettings = $this->integrationRepository->getLiratGitCards();

        $basePriceSource = $giftCardSettings->config['base_price_source'] ?? 'product_price';

        return $basePriceSource == 'product_price' ? 'productPrice' : 'sellPrice';
    }
}
