<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\User\ProductAvailabilitySubscriptionService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UpdateApiProductStock extends Command
{
    protected $signature = 'app:update-api-product-stock';

    protected $description = 'Update products stock availability from API';

    public function handle()
    {
        $this->info('Starting API stock update...');

        $apiProducts = $this->loadApiProducts();
        $apiProductsByExternalId = $apiProducts->keyBy('productId');

        $internalProducts = app(ProductRepository::class)->getApiProducts();
        $productAvailabilitySubscriptionService = app(ProductAvailabilitySubscriptionService::class);

        $now = Carbon::now();

        DB::transaction(function () use ($apiProductsByExternalId, $internalProducts, $now, $productAvailabilitySubscriptionService) {
            foreach ($internalProducts as $product) {
                $apiProduct = $apiProductsByExternalId->get($product->external_id);

                $wasInStock = $product->in_stock;

                $product->update([
                    'api_stock_available' => $apiProduct['available'],
                    'api_stock_last_checked_at' => $now,
                ]);

                if ($product->in_stock && !$wasInStock) {
                    $productAvailabilitySubscriptionService->notifySubscribers($product);
                }
            }
        });

        $this->info('API stock update completed successfully.');
    }


    /**
     * Load API products from JSON file and return all fields as-is.
     */
    private function loadApiProducts(): Collection
    {
        $file = storage_path('app/private/products_en.json');

        $products = json_decode(file_get_contents($file), true);

        return collect($products);
    }
}
