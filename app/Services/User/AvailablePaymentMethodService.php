<?php

namespace App\Services\User;

use App\Repositories\PaymentMethodRepository;

class AvailablePaymentMethodService
{
    public function __construct(private PaymentMethodRepository $paymentMethodRepository) {}

    public function getAvailableMethods(string $type)
    {
        if ($type === 'checkout') {
            return $this->getAvailableCheckoutPaymentMethods($type);
        }

        return $this->getAvailableTopUpMethods();
    }

    public function getAvailableCheckoutPaymentMethods()
    {
        $stripe = $this->paymentMethodRepository->getStripe();
        $wallet = $this->paymentMethodRepository->getWallet();

        $availableMethods = [];

        if ($stripe->is_active && $stripe->active_for_checkout) {
            $availableMethods[] = 'card';
        }

        if ($wallet->is_active && $wallet->active_for_checkout) {
            $availableMethods[] = 'wallet';
        }

        return $availableMethods;
    }

    public function getAvailableTopUpMethods(): array
    {
        $stripe = $this->paymentMethodRepository->getStripe();
        $ccPayment = $this->paymentMethodRepository->getCCPayment();

        $availableMethods = [];

        if ($stripe->is_active && $stripe->active_for_top_up) {
            $availableMethods[] = 'card';
        }

        if ($ccPayment->is_active && $ccPayment->active_for_top_up) {
            $availableMethods[] = 'usdt';
        }

        return $availableMethods;
    }
}
