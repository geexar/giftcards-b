<?php

namespace App\Services\Stripe;

use App\Enums\OrderStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Order;
use App\Models\User;
use App\Models\WebhookLog;
use App\Notifications\CardTopUpNotification;
use App\Repositories\OrderRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\User\OrderService;
use App\Services\User\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Stripe\Webhook;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class StripeWebhookService
{
    private object $credentials;

    public function __construct(
        private PaymentMethodRepository $paymentMethodRepository,
        private UserRepository $userRepository,
        private TransactionRepository $transactionRepository,
        private OrderRepository $orderRepository,
        private OrderService $orderService,
        private WalletService $walletService
    ) {
        $credentials = $this->paymentMethodRepository->getStripe()->activeCredentials;

        if (!$credentials) {
            throw new BadRequestHttpException('must setup stripe credentials');
        }

        $this->credentials = (object) $credentials->data;
    }

    public function handle(Request $request): void
    {
        $this->logWebhook($request);

        if ($request->type != 'checkout.session.completed') {
            return;
        }

        // 1️⃣ Verify signature and get Stripe Event object
        $event = $this->verifySignature($request);

        // 2️⃣ Access metadata safely
        $transactionType = $event->data->object->metadata->transaction_type;

        if ($transactionType === 'wallet_top_up') {
            $this->handleWalletTopup($event);
        }

        if ($transactionType === 'order_payment') {
            $this->handleOrderPayment($event);
        }
    }

    private function logWebhook(Request $request): void
    {
        WebhookLog::create([
            'provider' => 'stripe',
            'payload' => $request->all(),
        ]);
    }

    private function verifySignature(Request $request)
    {
        // 🚨 ONLY FOR LOCAL / TESTING
        // return json_decode($request->getContent());

        $endpointSecret = $this->credentials->webhook_secret;
        $payload = $request->getContent();
        $sigHeader = $request->header('stripe-signature');

        if (!$sigHeader) {
            throw new BadRequestHttpException('Missing Stripe-Signature header');
        }

        try {
            return Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Exception $e) {
            Log::error('Invalid Stripe signature: ' . $e->getMessage());
            throw new BadRequestHttpException('Invalid Stripe signature: ' . $e->getMessage());
        }
    }

    private function handleWalletTopup($event): void
    {
        $paymentObject = $event->data->object;
        $referenceId = $paymentObject->payment_intent;
        $userId = $paymentObject->metadata->user_id;
        $amount = $paymentObject->amount_total / 100;

        // Check idempotency inside transaction
        if ($this->transactionRepository->getByReferenceId($referenceId)) {
            return;
        }

        // Lock user row inside transaction
        $user = $this->userRepository->getByIdForUpdate($userId);

        if (!$user) {
            throw new BadRequestHttpException('user id not found');
        }

        $transaction = DB::transaction(function () use ($user, $referenceId, $amount) {
            $transaction = $this->walletService->createStripeTopUpTransaction($user, $amount, $referenceId);

            $user->balance = $this->transactionRepository->getUserBalance($user->id);
            $user->save();

            return $transaction;
        });

        // send notification to user
        Notification::send($user, new CardTopUpNotification($transaction));
    }

    private function handleOrderPayment($event): void
    {
        $paymentObject = $event->data->object;
        $referenceId = $paymentObject->payment_intent;

        $order = $this->orderRepository->getById($paymentObject->metadata->order_id);
        $cartId = $paymentObject->metadata->cart_id;

        // check if valid order
        if (!$order || $order->status != OrderStatus::WAITING_PAYMENT) {
            return;
        }

        DB::transaction(function () use ($order, $referenceId, $cartId) {
            $this->orderService->processStripePaidOrder($order, $referenceId, $cartId);
        });
    }
}
