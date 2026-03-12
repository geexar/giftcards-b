<?php

namespace App\Services\Stripe;

use App\Repositories\PaymentMethodRepository;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Exceptions\ThirdPartyServiceException;
use App\Models\Order;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StripeService
{
    private object $credentials;

    public function __construct(private PaymentMethodRepository $paymentMethodRepository)
    {
        $credentials = $this->paymentMethodRepository->getStripe()->activeCredentials;

        if (!$credentials) {
            throw new BadRequestHttpException('must setup stripe credentials');
        }

        $this->credentials = (object) $credentials->data;

        Stripe::setApiKey($this->credentials->secret_key);
    }

    /**
     * Generate Stripe Checkout Payment URL
     */
    public function createTopUpPaymentUrl(string $userId, float $amount, string $successUrl, string $cancelUrl): string
    {
        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Wallet Topup',
                        ],
                        'unit_amount' => (int) ($amount * 100)
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'transaction_type' => 'wallet_top_up',
                    'user_id'          => $userId,
                ],
            ]);

            return $session->url;
        } catch (\Throwable $e) {
            throw new ThirdPartyServiceException($e->getMessage());
        }
    }

    public function createCheckoutPaymentUrl(Order $order, string $cartId, string $successUrl, string $cancelUrl): string
    {
        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => 'Order Payment',
                            ],
                            'unit_amount' => (int) ($order->total * 100),
                        ],
                        'quantity' => 1,
                    ],
                ],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => [
                    'transaction_type' => 'order_payment',
                    'order_id' => $order->id,
                    'cart_id' => $cartId,
                ],
            ]);
            return $session->url;
        } catch (\Throwable $e) {
            throw new ThirdPartyServiceException($e->getMessage());
        }
    }
}
