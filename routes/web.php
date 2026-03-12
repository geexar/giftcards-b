<?php

use App\Http\Controllers\Webhooks\CCPaymentWebhookController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use App\Models\Order;
use App\Services\Lirat\LiratGiftCardService;
use App\Services\User\OrderStatusService;
use App\User\ProfitCaclulationService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Clear cache route for non-production environments
if (! App::environment('production')) {
    Route::get('clear-cache', function () {
        Artisan::call('cache:clear');

        return 'cache cleared';
    });
}

// ccpayment
Route::any('ccpayment/webhook', CCPaymentWebhookController::class);

// stripe
Route::any('stripe/webhook', StripeWebhookController::class);
