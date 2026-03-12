<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\CCPayment\CCPaymentWebhookService;
use Illuminate\Http\Request;

class CCPaymentWebhookController extends Controller
{
    public function __construct(private CCPaymentWebhookService $ccpaymentWebhookService) {}

    public function __invoke(Request $request)
    {
        $this->ccpaymentWebhookService->handle($request);

        return response()->json(['status' => 'ok']);
    }
}
