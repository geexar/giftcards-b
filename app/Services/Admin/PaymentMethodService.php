<?php

namespace App\Services\Admin;

use App\Repositories\PaymentMethodRepository;
use App\Services\ActivityLogService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PaymentMethodService
{
    public function __construct(
        private PaymentMethodRepository $paymentMethodRepository,
        private ActivityLogService $activityLogService
    ) {}

    public function updateWallet(array $data)
    {
        $paymentMethod = $this->paymentMethodRepository->getWallet();

        DB::transaction(function () use ($paymentMethod, $data) {
            $this->paymentMethodRepository->update($paymentMethod, $data);

            $this->activityLogService->store($paymentMethod, 'payment_method.updated');
        });
    }

    public function updateStripe(array $data): void
    {
        $paymentMethod = $this->paymentMethodRepository->getStripe();

        // make payment method active if active for checkout or top up
        $data['is_active'] = ($data['active_for_checkout'] ?? false)
            || ($data['active_for_top_up'] ?? false);

        // update only general config fields
        $generalData = Arr::only($data, [
            'is_active',
            'active_for_checkout',
            'active_for_top_up',
            'active_mode',
        ]);

        DB::transaction(function () use ($paymentMethod, $generalData, $data) {

            // 1. Update general payment method config
            $this->paymentMethodRepository->update($paymentMethod, $generalData);

            // 2. Update credentials ONLY if active_mode exists
            if (isset($data['active_mode'])) {
                $activeMode = $data['active_mode'];

                if (isset($data[$activeMode])) {
                    $paymentMethod->credentials()->updateOrCreate(
                        ['mode' => $activeMode],
                        ['data' => $data[$activeMode]]
                    );
                }
            }

            $this->activityLogService->store($paymentMethod, 'payment_method.updated');
        });
    }


    public function updateUsdt(array $data): void
    {
        $paymentMethod = $this->paymentMethodRepository->getCCPayment();

        // Update only allowed general fields
        $generalData = Arr::only($data, [
            'is_active',
            'active_mode',
        ]);

        DB::transaction(function () use ($paymentMethod, $generalData, $data) {

            // 1. Update payment method config
            if (! empty($generalData)) {
                $this->paymentMethodRepository->update($paymentMethod, $generalData);
            }

            // 2. Update credentials ONLY if active_mode is sent
            if (isset($data['active_mode'])) {
                $activeMode = $data['active_mode'];

                if (isset($data[$activeMode])) {
                    $paymentMethod->credentials()->updateOrCreate(
                        ['mode' => $activeMode],
                        ['data' => $data[$activeMode]]
                    );
                }
            }

            $this->activityLogService->store($paymentMethod, 'payment_method.updated');
        });
    }

    public function getPaymentMethodName(string $paymentMethodId)
    {
        $paymentMethodId = (int) $paymentMethodId;

        return match ($paymentMethodId) {
            1 => __('Wallet'),
            2 => __('Credit Card'),
            3 => __('USDT'),
            default => null,
        };
    }
}
