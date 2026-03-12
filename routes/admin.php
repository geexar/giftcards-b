<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\Auth\AuthenticationController;
use App\Http\Controllers\Admin\Auth\PasswordResetController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\DdlController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GroupedNotificationController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\InventoryProductController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StaticPageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductStatusManagerController;
use App\Http\Controllers\Admin\ProductSyncController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UsdtAddressController;
use App\Http\Controllers\Admin\UsdtNetworkController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->prefix('auth')->group(function () {
    Route::post('login', [AuthenticationController::class, 'login']);
    Route::post('send-reset-code', [PasswordResetController::class, 'sendResetCode']);
    Route::post('allowed-to-send-reset-code', [PasswordResetController::class, 'allowedToSendResetCode']);
    Route::post('confirm-reset-code', [PasswordResetController::class, 'confirmResetCode']);
    Route::post('reset-password', [PasswordResetController::class, 'resetPassword']);
});

Route::post('auth/logout', [AuthenticationController::class, 'logout'])->middleware('auth:admin');

Route::middleware(['auth:admin', 'activeToken:admin'])->group(function () {

    Route::get('home', [HomeController::class, 'index']);
    Route::get('top-products', [HomeController::class, 'topProducts']);
    Route::get('home/excel-export/order-status-count', [HomeController::class, 'exportExcelOrderStatusCount']);
    Route::get('home/excel-export/customer-segments', [HomeController::class, 'exportCustomerSegments']);
    Route::get('home/excel-export/top-products', [HomeController::class, 'exportTopProducts']);
    Route::get('home/excel-export/refund-summary', [HomeController::class, 'exportRefundSummary']);

    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    Route::post('users/{user}/update-balance', [UserController::class, 'updateBalance']);

    Route::apiResource('admins', AdminController::class);
    Route::post('admins/{admin}/toggle-status', [AdminController::class, 'toggleStatus']);

    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus']);
    Route::get('permissions', [PermissionController::class, 'index']);

    Route::apiResource('categories', CategoryController::class);
    Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus']);
    Route::get('category-tree', [CategoryController::class, 'categoryTree']);

    Route::apiResource('products', ProductController::class);
    Route::post('products/bulk-actions/status', [ProductController::class, 'bulkStatusUpdate']);
    Route::get('import-products-template', [ProductController::class, 'importTemplate']);
    Route::post('import-products', [ProductController::class, 'import']);
    Route::post('api-products/sync', [ProductSyncController::class, 'sync']);
    Route::get('api-products/sync-logs', [ProductSyncController::class, 'syncLogs']);
    Route::get('api-products/not-viewed-count', [ProductSyncController::class, 'notViewedApiProductsCount']);
    Route::get('api-products/is-sync-in-progress', [ProductSyncController::class, 'isSyncInProgress']);

    Route::get('product-status-manager/categories', [ProductStatusManagerController::class, 'categories']);
    Route::get('product-status-manager/products', [ProductStatusManagerController::class, 'products']);
    Route::post('product-status-manager/{category_id}/toggle-category-status', [ProductStatusManagerController::class, 'toggleCategoryStatus']);
    Route::post('product-status-manager/{product_id}/update-product-status', [ProductStatusManagerController::class, 'updateProductStatus']);

    Route::apiResource('orders', OrderController::class)->only('index', 'show', 'update');
    Route::get('orders/{order}/logs', [OrderController::class, 'logs']);
    Route::get('orders/{order}/logs/export/pdf', [OrderController::class, 'exportOrderActivityLogsPdf']);
    Route::post('orders/{order}/update-notes', [OrderController::class, 'updateNotes']);
    Route::get('need-action-orders-count', [OrderController::class, 'getNeedActionOrdersCount']);
    Route::get('orders/export/excel', [OrderController::class, 'exportOrdersExcel']);
    Route::get('orders/{order}/export/pdf', [OrderController::class, 'exportOrderPdf']);


    Route::apiResource('transactions', TransactionController::class)->only('index');
    Route::get('transactions/export/excel', [TransactionController::class, 'exportExcel']);
    Route::apiResource('refunds', RefundController::class)->only('index', 'show', 'update');
    Route::get('refunds/export/excel', [RefundController::class, 'exportExcel']);

    Route::apiResource('inventory-products', InventoryProductController::class)->only('index', 'show');
    Route::post('inventory-products/{inventory_product}/clear-stock', [InventoryProductController::class, 'clearProductStock']);
    Route::post('inventory-products/{inventory_product}/restock', [InventoryProductController::class, 'restockProduct']);

    Route::apiResource('banners', BannerController::class);
    Route::post('banners/{banner}/toggle-status', [BannerController::class, 'toggleStatus']);

    Route::apiResource('faqs', FaqController::class);
    Route::post('faqs/{faq}/toggle-status', [FaqController::class, 'toggleStatus']);

    Route::apiResource('articles', ArticleController::class);
    Route::post('articles/{article}/toggle-status', [ArticleController::class, 'toggleStatus']);

    Route::apiResource('static-pages', StaticPageController::class)->only('index', 'show', 'update');

    Route::apiResource('countries', CountryController::class)->only('index', 'show', 'update');
    Route::post('countries/{country}/toggle-status', [CountryController::class, 'toggleStatus']);

    Route::apiResource('contact-messages', ContactMessageController::class)->only('index', 'show');
    Route::apiResource('activity-logs', ActivityLogController::class)->only('index');

    Route::apiResource('usdt-networks', UsdtNetworkController::class);
    Route::get('available-usdt-networks', [UsdtNetworkController::class, 'availableNetworks']);

    Route::get('payment-methods', [PaymentMethodController::class, 'index']);
    Route::post('payment-methods/wallet', [PaymentMethodController::class, 'updateWallet']);
    Route::post('payment-methods/stripe', [PaymentMethodController::class, 'updateStripe']);
    Route::post('payment-methods/usdt', [PaymentMethodController::class, 'updateUsdt']);

    Route::get('usdt-addresses', [UsdtAddressController::class, 'index']);

    Route::get('settings', [SettingController::class, 'index']);
    Route::post('settings/inventory', [SettingController::class, 'updateInventory']);
    Route::post('settings/order-limits', [SettingController::class, 'updateOrderLimits']);
    Route::post('settings/markup-fee', [SettingController::class, 'updateMarkupFee']);
    Route::post('settings/gift-cards', [SettingController::class, 'updateGiftCards']);
    Route::post('settings/social-media', [SettingController::class, 'updateSocialMedia']);
    Route::post('settings/contact-support', [SettingController::class, 'updateContactSupport']);

    Route::apiResource('group-notifications', GroupedNotificationController::class)->only('index', 'store');

    Route::get('profile', [ProfileController::class, 'index']);
    Route::post('profile', [ProfileController::class, 'update']);
    Route::post('profile/app-locale', [ProfileController::class, 'updateAppLocale']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('notifications/mark-read', [NotificationController::class, 'markAsRead']);

    Route::prefix('ddl')->controller(DdlController::class)->group(function () {
        Route::get('roles', 'roles');
        Route::get('countries', 'countries');
        Route::get('categories', 'categories');
        Route::get('payment-methods', 'paymentMethods');
        Route::get('usdt-networks', 'usdtNetworks');
    });
});
