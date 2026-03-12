<?php

namespace App\Services\User;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\User;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\TransactionRepository;
use App\Services\Admin\TransactionService;
use App\Services\Stripe\StripeService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WalletService
{
    public function __construct(
        private PaymentMethodRepository $paymentMethodRepository,
        private StripeService $stripeService,
        private TransactionService $transactionService,
        private TransactionRepository $transactionRepository
    ) {}

    public function getTopUpPaymentUrl(float $amount)
    {
        $stripe = $this->paymentMethodRepository->getStripe();

        if (!$stripe->active_for_top_up) {
            throw new BadRequestHttpException('TopUp via card is not available currently.');
        }

        $frontendUrl = config('app.frontend_url');

        return $this->stripeService->createTopUpPaymentUrl(
            userId: auth('user')->id(),
            amount: $amount,
            successUrl: "$frontendUrl/my-wallet?topup_status=success",
            cancelUrl: "$frontendUrl/my-wallet?topup_status=canceled"
        );
    }

    public function createStripeTopUpTransaction(User $user, float $amount, string $referenceId)
    {
        return $this->transactionRepository->create([
            'transaction_no' => $this->transactionService->generateTransactionNo(),
            'type' => TransactionType::TOPUP,
            'user_id' => $user->id,
            'actor_type' => User::class,
            'actor_id' => $user->id,
            'amount' => $amount,
            'reference_id' => $referenceId,
            'status' => TransactionStatus::SUCCESS,
            'payment_method_id' => 2,
            'affects_wallet' => true,
        ]);
    }

    public function createUsdtTopUpTransaction(User $user, float $amount, string $referenceId, string $chain)
    {
        return $this->transactionRepository->create([
            'transaction_no' => $this->transactionService->generateTransactionNo(),
            'type' => TransactionType::TOPUP,
            'user_id' => $user->id,
            'actor_type' => User::class,
            'actor_id' => $user->id,
            'amount' => $amount,
            'reference_id' => $referenceId,
            'status' => TransactionStatus::SUCCESS,
            'payment_method_id' => 3,
            'usdt_network' => $chain,
            'affects_wallet' => true,
        ]);
    }
}
