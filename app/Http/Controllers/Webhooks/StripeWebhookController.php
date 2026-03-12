<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\Stripe\StripeWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(private StripeWebhookService $stripeWebhookService) {}

    public function __invoke(Request $request)
    {
        $this->stripeWebhookService->handle($request);

        return response()->json(['status' => 'ok']);
    }
}
