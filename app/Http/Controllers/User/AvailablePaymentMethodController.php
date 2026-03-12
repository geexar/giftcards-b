<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\GetAvailablePaymentMethodsRequest;
use App\Services\User\AvailablePaymentMethodService;
use Illuminate\Http\Request;

class AvailablePaymentMethodController extends Controller
{
    public function __construct(private AvailablePaymentMethodService $availablePaymentMethodService) {}

    public function __invoke(GetAvailablePaymentMethodsRequest $request)
    {
        $availableMethods = $this->availablePaymentMethodService->getAvailableMethods($request->type);

        return success($availableMethods);
    }
}
