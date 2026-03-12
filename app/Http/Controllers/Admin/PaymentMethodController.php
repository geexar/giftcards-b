<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PaymentMethods\StripeSettingsRequest;
use App\Http\Requests\Admin\PaymentMethods\UsdtSettingsRequest;
use App\Http\Requests\Admin\PaymentMethods\WalletSettingsRequest;
use App\Http\Resources\Admin\PaymentMethodResource;
use App\Repositories\PaymentMethodRepository;
use App\Services\Admin\PaymentMethodService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PaymentMethodController extends Controller implements HasMiddleware
{
    public function __construct(
        private PaymentMethodService $paymentMethodService,
        private PaymentMethodRepository $paymentMethodRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:update payment methods', except: [])
        ];
    }

    public function index()
    {
        $paymentMethods = $this->paymentMethodRepository->getAll();

        return success(PaymentMethodResource::collection($paymentMethods));
    }

    public function updateWallet(WalletSettingsRequest $request)
    {
        $this->paymentMethodService->updateWallet($request->validated());

        return success(true);
    }

    public function updateStripe(StripeSettingsRequest $request)
    {
        $this->paymentMethodService->updateStripe($request->validated());

        return success(true);
    }

    public function updateUsdt(UsdtSettingsRequest $request)
    {
        $this->paymentMethodService->updateUsdt($request->validated());

        return success(true);
    }
}
