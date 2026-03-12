<?php

use App\Http\Controllers\User\ContactChannelController;
use App\Http\Controllers\User\ArticleController;
use App\Http\Controllers\User\Auth\AuthenticationController;
use App\Http\Controllers\User\Auth\EmailVerificaionController;
use App\Http\Controllers\User\Auth\OAuthController;
use App\Http\Controllers\User\Auth\PasswordResetController;
use App\Http\Controllers\User\Auth\RegistrationController;
use App\Http\Controllers\User\AvailablePaymentMethodController;
use App\Http\Controllers\User\BannerController;
use App\Http\Controllers\User\CartController;
use App\Http\Controllers\User\CategoryController;
use App\Http\Controllers\User\ContactMessageController;
use App\Http\Controllers\User\DdlController;
use App\Http\Controllers\User\FaqController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\NotificationController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\ProductController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\StaticPageController;
use App\Http\Controllers\User\TransactionController;
use App\Http\Controllers\User\WalletController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::prefix('auth')->group(function () {
    Route::middleware('guest:user')->group(function () {
        Route::post('register', [RegistrationController::class, 'store']);
        Route::post('login', [AuthenticationController::class, 'login']);
        Route::post('oauth/{provider}', [OAuthController::class, 'handle']);
        Route::post('oauth-apple-email', [OAuthController::class, 'addAppleContactEmail']);
    });

    Route::post('send-reset-code', [PasswordResetController::class, 'sendResetCode']);
    Route::post('confirm-reset-code', [PasswordResetController::class, 'confirmResetCode']);
    Route::post('allowed-to-send-reset-code', [PasswordResetController::class, 'allowedToSendCode']);
    Route::post('reset-password', [PasswordResetController::class, 'resetPassword']);

    Route::post('send-email-verification-code', [EmailVerificaionController::class, 'sendVerificationCode']);
    Route::post('verify-email', [EmailVerificaionController::class, 'verify']);
    Route::post('allowed-to-send-email-verfication-code', [EmailVerificaionController::class, 'allowedToSendVerificationCode']);

    Route::post('logout', [AuthenticationController::class, 'logout'])->middleware('auth:user');
});

Route::get('banners', BannerController::class);
Route::get('faqs', FaqController::class);
Route::apiResource('articles', ArticleController::class);
Route::apiResource('static-pages', StaticPageController::class)->only('index', 'show');
Route::get('contact-channels', ContactChannelController::class);
Route::get('available-payment-methods', AvailablePaymentMethodController::class);
Route::post('contact-message', ContactMessageController::class);

Route::apiResource('categories', CategoryController::class)->only('index', 'show');
Route::get('main-categories', [CategoryController::class, 'mainCategories']);
Route::get('sub-categories', [CategoryController::class, 'subCategories']);

Route::apiResource('products', ProductController::class)->only('index', 'show');
Route::get('products/{product}/suggested-products', [ProductController::class, 'suggestedProducts']);
Route::post('subscribe-product-availability', [ProductController::class, 'subscribeProductAvailability']);

Route::get('cart', [CartController::class, 'get']);
Route::post('cart/increase', [CartController::class, 'increase']);
Route::post('cart/decrease', [CartController::class, 'decrease']);
Route::post('cart/delete', [CartController::class, 'delete']);
Route::post('cart/pre-checkout-validation', [CartController::class, 'preCheckoutValidation']);

Route::post('orders', [OrderController::class, 'store']);
Route::get('orders/{order}/status-info', [OrderController::class, 'statusInfo']);

Route::get('search', [HomeController::class, 'search']);
Route::get('promoted-categories', [HomeController::class, 'promotedCategories']);
Route::get('featured-products', [HomeController::class, 'featuredProducts']);
Route::get('best-seller-products', [HomeController::class, 'bestSellerProducts']);
Route::get('discounted-products', [HomeController::class, 'discountedProducts']);
Route::get('popular-products', [HomeController::class, 'popularProducts']);
Route::get('trending-entites', [HomeController::class, 'trendingEntities']);
Route::get('featured-category', [HomeController::class, 'featuredCategoryItems']);

Route::middleware('auth:user', 'activeToken:user')->group(function () {

    Route::apiResource('orders', OrderController::class)->only('index', 'show');
    Route::post('orders/{order}/cancel-order', [OrderController::class, 'cancelOrder']);
    Route::post('orders/{order_item}/cancel-item', [OrderController::class, 'cancelOrderItem']);
    Route::post('orders/{order}/rate', [OrderController::class, 'rate']);

    Route::get('transactions', [TransactionController::class, 'index']);

    Route::get('balance', [WalletController::class, 'balance']);
    Route::get('usdt-networks', [WalletController::class, 'getUsdtNetworks']);
    Route::get('usdt-address', [WalletController::class, 'getUsdtAddress']);
    Route::post('usdt-address', [WalletController::class, 'createUsdtAddress']);
    Route::post('topup-payment-url', [WalletController::class, 'getTopupPaymentUrl']);

    Route::get('profile', [ProfileController::class, 'index']);
    Route::post('profile', [ProfileController::class, 'update']);
    Route::post('profile/send-update-email-verification-code', [EmailVerificaionController::class, 'sendUpdateEmailVerificationCode']);
    Route::post('profile/update-email', [ProfileController::class, 'updateEmail']);
    Route::post('profile/update-image', [ProfileController::class, 'updateImage']);
    Route::post('profile/update-password', [ProfileController::class, 'updatePassword']);
    Route::post('profile/add-password', [ProfileController::class, 'addPassword']);
    Route::post('profile/app-locale', [ProfileController::class, 'updateAppLocale']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('notifications/mark-read', [NotificationController::class, 'markAsRead']);
});

Route::prefix('ddl')->controller(DdlController::class)->group(function () {
    Route::get('countries', 'countries');
});
