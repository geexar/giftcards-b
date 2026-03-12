<?php

namespace App\Repositories;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Enums\StockStatus;
use App\Models\Product;
use App\Models\ProductVariantValue;
use Illuminate\Support\Facades\DB;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function getByIdForUpdate(string $id)
    {
        return $this->model->where('id', $id)->lockForUpdate()->first();
    }

    public function getLocalProducts()
    {
        return $this->model->where('source', 'local')->get();
    }

    public function getApiProducts()
    {
        return $this->model->where('source', 'api')->get();
    }

    public function getByExternalId(string $externalId)
    {
        return $this->model->where('external_id', $externalId)->first();
    }

    public function getBySKU(string $sku)
    {
        return $this->model->where('sku', $sku)->first();
    }

    public function getPaginatedProductsForDashboard()
    {
        return $this->model
            ->when(request('source'), fn($query) => $query->where('source', request('source')))
            ->when(request('category_id'), fn($query) => $query->where('category_id', request('category_id')))
            ->when(request('search'), function ($query, $search) {
                return $query->where('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ar', 'like', "%{$search}%");
            })
            ->when(request('status'), fn($query) => $query->where('status', request('status')))
            ->when(request('delivery_type'), fn($query) => $query->where('delivery_type', request('delivery_type')))
            ->when(request()->has('in_stock'), function ($q) {
                if (request('in_stock')) {
                    $this->filterInStock($q);
                } else {
                    $this->filterOutOfStock($q);
                }
            })
            ->when(request('from_date'), fn($query) => $query->whereDate('created_at', '>=', request('from_date')))
            ->when(request('to_date'), fn($query) => $query->whereDate('created_at', '<=', request('to_date')))
            ->when(request('creation_range'), function ($query, $period) {
                match ($period) {
                    'today' => $query->whereDate('created_at', now()),
                    'last_7_days' => $query->whereDate('created_at', '>=', now()->subDays(7)),
                    'last_30_days' => $query->whereDate('created_at', '>=', now()->subDays(30)),
                    'this_month' => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                    default => null
                };
            })
            ->when(request()->has('is_global'), fn($query) => $query->where('is_global', true))
            ->when(request('selected_countries'), function ($query, $countries) {
                $query->whereHas('countries', function ($q) use ($countries) {
                    $q->whereIn('countries.id', (array) $countries);
                });
            })
            ->when(request()->has('is_best_seller'), fn($query) => $query->where('is_best_seller', true))
            ->when(request()->has('is_popular'), fn($query) => $query->where('is_popular', true))
            ->when(request()->has('is_featured'), fn($query) => $query->where('is_featured', true))
            ->when(request()->has('is_trending'), fn($query) => $query->where('is_trending', true))
            ->with('variant.values.variant.product', 'countries')
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    private function filterInStock($query)
    {
        // Filter products that ARE in stock
        $query->where(function ($q) {
            $q->where('has_variants', false)
                ->where(function ($q2) {
                    $q2->where('delivery_type', DeliveryType::INSTANT->value)
                        ->whereHas('validCodes');

                    $q2->orWhere(function ($q3) {
                        $q3->where('delivery_type', DeliveryType::REQUIRES_CONFIRMATION->value)
                            ->where('manual_stock', '>', 0);
                    });
                });

            $q->orWhere('has_variants', true)
                ->whereHas('variant.values', function ($q4) {
                    $q4->where(function ($q5) {
                        $q5->where('delivery_type', DeliveryType::INSTANT->value)
                            ->whereHas('validCodes');

                        $q5->orWhere(function ($q6) {
                            $q6->where('delivery_type', DeliveryType::REQUIRES_CONFIRMATION->value)
                                ->where('manual_stock', '>', 0);
                        });
                    });
                });
        });
    }

    private function filterOutOfStock($query)
    {
        // Filter products that are OUT of stock
        $query->where(function ($q) {
            $q->where('has_variants', false)
                ->where(function ($q2) {
                    $q2->where(function ($q3) {
                        $q3->where('delivery_type', DeliveryType::INSTANT->value)
                            ->doesntHave('validCodes');
                    });

                    $q2->orWhere(function ($q3) {
                        $q3->where('delivery_type', DeliveryType::REQUIRES_CONFIRMATION->value)
                            ->where('manual_stock', '<=', 0);
                    });
                });

            $q->orWhere('has_variants', true)
                ->whereDoesntHave('variant.values', function ($q4) {
                    $q4->where(function ($q5) {
                        $q5->where(function ($q6) {
                            $q6->where('delivery_type', DeliveryType::INSTANT->value)
                                ->whereHas('validCodes');
                        });

                        $q5->orWhere(function ($q6) {
                            $q6->where('delivery_type', DeliveryType::REQUIRES_CONFIRMATION->value)
                                ->where('manual_stock', '>', 0);
                        });
                    });
                });
        });
    }

    public function getPaginatedProductsForStatusManager()
    {
        return $this->model
            ->when(request('source'), fn($query) => $query->where('source', request('source')))
            ->when(request('category_id'), fn($query) => $query->where('category_id', request('category_id')))
            ->when(request('search'), function ($query, $search) {
                return $query->where('name->en', 'like', "%{$search}%")
                    ->orWhere('name->ar', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getProductsForWebsite()
    {
        $query = $this->model
            ->where('status', ProductStatus::ACTIVE->value)
            ->withActiveCategory()
            ->where('category_id', request('category_id'))
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%");
                });
            })
            ->when(request()->has('is_best_seller'), fn($query) => $query->where('is_best_seller', true))
            ->when(request()->has('is_popular'), fn($query) => $query->where('is_popular', true))
            ->when(request()->has('is_featured'), fn($query) => $query->where('is_featured', true))
            ->when(request()->has('is_trending'), fn($query) => $query->where('is_trending', true))
            ->with('variant.values.variant.product', 'media')
            ->latest();

        return request()->boolean('paginated')
            ? $query->paginate(page: request('page'), perPage: request('per_page'))
            : $query->get();
    }

    public function getNotViewdApiProductsCount()
    {
        return $this->model
            ->where('source', 'api')
            ->where('viewed_by_admin', false)
            ->count();
    }

    public function getPaginatedInventoryProducts()
    {
        $threshold = getSetting('inventory', 'stock_threshold');

        return $this->model
            ->where('source', 'local')
            ->where('status', '!=', ProductStatus::DRAFTED->value)
            ->withCount('validCodes') // product valid codes count
            ->with(['variant.values' => fn($q) => $q->withCount('validCodes')]) // load variant values with validCodes count
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name->en', 'like', "%{$search}%")
                        ->orWhere('name->ar', 'like', "%{$search}%");
                });
            })
            ->when(request('stock_status'), function ($query, $status) use ($threshold) {
                if ($status === StockStatus::OUT_OF_STOCK->value) {
                    $this->filterOutOfStock($query);
                } elseif ($status === StockStatus::LOW->value) {
                    $this->filterLowStock($query, $threshold);
                } elseif ($status === StockStatus::NORMAL->value) {
                    $this->filterNormalStock($query, $threshold);
                }
            })
            ->with('variant.values', 'media')
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    /**
     * Filter products that are low stock
     */
    private function filterLowStock($query, int $threshold)
    {
        $query->where(function ($q) use ($threshold) {
            $stockSql = $this->getRawStockCalculationSql();

            $q->whereRaw("($stockSql) > 0")
                ->whereRaw("($stockSql) < ?", [$threshold]);
        });
    }

    private function filterNormalStock($query, int $threshold)
    {
        $query->whereRaw("({$this->getRawStockCalculationSql()}) >= ?", [$threshold]);
    }

    /**
     * Generates a SQL subquery that calculates total stock regardless of variant status.
     */
    private function getRawStockCalculationSql(): string
    {
        $today = now()->toDateString();

        // Subquery for codes count
        $codesCountSql = "SELECT COUNT(*) FROM codes 
                      WHERE codes.codeable_id = %s 
                      AND codes.codeable_type = '%s' 
                      AND codes.is_used = 0 
                      AND (codes.expiry_date IS NULL OR codes.expiry_date > '$today')";

        $productCodes = sprintf($codesCountSql, 'products.id', addslashes(Product::class));
        $variantCodes = sprintf($codesCountSql, 'pvv.id', addslashes(ProductVariantValue::class));

        return "
        CASE 
            WHEN products.has_variants = 0 THEN 
                (COALESCE(products.manual_stock, 0) + ($productCodes))
            ELSE 
                COALESCE((
                    SELECT SUM(COALESCE(pvv.manual_stock, 0) + ($variantCodes))
                    FROM product_variant_values pvv
                    JOIN product_variants pv ON pv.id = pvv.product_variant_id
                    WHERE pv.product_id = products.id
                    AND pvv.deleted_at IS NULL
                    AND pv.deleted_at IS NULL
                ), 0)
        END";
    }



    public function searchedProducts(string $searchQuery)
    {
        return $this->model
            ->withActiveCategory()
            ->where('status', ProductStatus::ACTIVE->value)
            ->where(function ($q) use ($searchQuery) {
                $q->where('name->en', 'like', "%{$searchQuery}%")
                    ->orWhere('name->ar', 'like', "%{$searchQuery}%");
            })
            ->limit(10)
            ->get();
    }

    public function getFeaturedProducts()
    {
        return $this->model
            ->withActiveCategory()
            ->where('status', ProductStatus::ACTIVE->value)
            ->where('is_featured', true)
            ->with('variant.values.variant.product', 'media')
            ->get();
    }

    public function getSuggestedProducts(Product $product, ?int $limit = 10)
    {
        return $this->model
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->with('variant.firstVisibleValue.variant.product', 'media')
            ->limit($limit)
            ->get();
    }

    public function getDiscountedProducts()
    {
        return $this->model
            ->withActiveCategory()
            ->where(function ($q) {
                $q->where('has_discount', true)
                    ->orWhereHas('variant.values', fn($v) => $v->where('has_discount', true));
            })
            ->where('status', ProductStatus::ACTIVE->value)
            ->with('variant.firstVisibleValue.variant.product', 'media')
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getPopularProducts()
    {
        return $this->model
            ->withActiveCategory()
            ->where('status', ProductStatus::ACTIVE->value)
            ->where('is_popular', true)
            ->with('variant.firstVisibleValue.variant.product', 'media')
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getTrendingProducts()
    {
        return $this->model
            ->withActiveCategory()
            ->where('status', ProductStatus::ACTIVE->value)
            ->where('is_trending', true)
            ->get();
    }

    public function getBestSellerProducts()
    {
        $query = $this->model
            ->withActiveCategory()
            ->where('status', ProductStatus::ACTIVE->value)
            ->where('is_best_seller', true);

        return request()->boolean('paginated')
            ? $query->paginate(page: request('page'), perPage: request('per_page'))
            : $query->get();
    }

    public function trendingCount(?string $exceptedId = null)
    {
        return $this->model
            ->where('is_trending', true)
            ->where('id', '!=', $exceptedId)
            ->count();
    }

    public function popularCount(?string $exceptedId = null)
    {
        return $this->model
            ->where('is_popular', true)
            ->where('id', '!=', $exceptedId)
            ->count();
    }

    public function featuredCount(?string $exceptedId = null)
    {
        return $this->model
            ->where('is_featured', true)
            ->where('id', '!=', $exceptedId)
            ->count();
    }

    public function getPaginatedCategoryProducts(string $categoryId)
    {
        return $this->model
            ->where('category_id', $categoryId)
            ->where('status', ProductStatus::ACTIVE->value)
            ->with('media')
            ->latest()
            ->paginate(page: request('page'), perPage: request('per_page'));
    }

    public function getTopProducts()
    {
        $sortBy = request('sort_by', 'quantity_sold');
        $sortOrder = request('sort_order', 'DESC');

        $sortColumn = match ($sortBy) {
            'quantity_sold' => 'quantity_sold',
            'net_revenue' => 'net_revenue',
            'total_profit' => 'total_profit',
            default => 'quantity_sold',
        };

        $products = $this->model
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNotIn('orders.status', [OrderStatus::WAITING_PAYMENT->value, OrderStatus::CANCELED->value])
            ->when(request('from_date'), fn($query) => $query->whereDate('orders.created_at', '>=', request('from_date')))
            ->when(request('to_date'), fn($query) => $query->whereDate('orders.created_at', '<=', request('to_date')))
            ->when(request('creation_range'), function ($query, $period) {
                match ($period) {
                    'today' => $query->whereDate('orders.created_at', now()),
                    'last_7_days' => $query->whereDate('orders.created_at', '>=', now()->subDays(7)),
                    'last_30_days' => $query->whereDate('orders.created_at', '>=', now()->subDays(30)),
                    'this_month' => $query
                        ->whereMonth('orders.created_at', now()->month)
                        ->whereYear('orders.created_at', now()->year),
                    default => null
                };
            })
            ->select([
                'products.id',
                'products.source',
                'products.status',
                DB::raw("products.name"),
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.total) as revenue'),
                DB::raw('SUM(order_items.total - (order_items.user_facing_price * order_items.fulfilled_quantity)) as net_revenue'),
                DB::raw('SUM(order_items.quantity * (order_items.user_facing_price - COALESCE(order_items.provider_original_price, order_items.price))) as total_profit'),
            ])
            ->groupBy('products.id', 'products.source', 'products.name', 'products.status')
            ->orderByRaw("$sortColumn $sortOrder")
            ->limit(10)
            ->get();

        return $products;
    }

    public function countByCategoryIds(array $categoryIds): int
    {
        return $this->model
            ->whereIn('category_id', $categoryIds)
            ->count();
    }
}
