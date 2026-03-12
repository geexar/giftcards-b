<?php

namespace App\Services\CCPayment;

use App\Exceptions\ThirdPartyServiceException;
use App\Models\WebhookLog;
use App\Notifications\UsdtTopUpNotification;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\UsdtAddressRepository;
use App\Repositories\UsdtNetworkRepository;
use App\Services\User\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CCPaymentWebhookService
{
    protected object $credentials;

    public function __construct(
        private PaymentMethodRepository $paymentMethodRepository,
        private TransactionRepository $transactionRepository,
        private UsdtNetworkRepository $usdtNetworkRepository,
        private WalletService $walletService,
        private UsdtAddressRepository $usdtAddressRepository
    ) {
        $credentials = $this->paymentMethodRepository->getCCPayment()->activeCredentials;

        if (!$credentials) {
            throw new BadRequestHttpException('must setup ccpayment credentials');
        }

        $this->credentials = (object) $credentials->data;

        Http::fake([
            'https://ccpayment.com/ccpayment/v2/getAppDepositRecord' => Http::response([
                "code" => 10000,
                "msg" => "success",
                "data" => [
                    "record" => [
                        "recordId" => "20250116073333231508600365121536",
                        "orderId" => "1737011983",
                        "coinId" => 1482,
                        "coinSymbol" => "TRX",
                        "chain" => "TRX",
                        "contract" => "TRX",
                        "coinUSDPrice" => "0.23717",
                        "fromAddress" => "TRPKg7eGMy9aZS2RumSPeWoyVkDqTVwLgL",
                        "toAddress" => "0x7E52bcadEe5b6aBa5B6a0D3b9d86140e769C046A",
                        "toMemo" => "",
                        "amount" => "5.4",
                        "serviceFee" => "0.0025",
                        "txId" => "f39abf3275607fe2ffd40c06adf877f249829f6d1146a4f72ca2ad79ed7ed072",
                        "status" => "Success",
                        "arrivedAt" => 1737012813,
                        "isFlaggedAsRisky" => false
                    ]
                ]
            ], 200),
        ]);
    }

    public function handle(Request $request): void
    {
        $this->logWebhook($request);

        $this->verifySignature($request);

        if ($request->type != 'DirectDeposit' || $request->msg['status'] != 'Success') {
            return;
        }

        $recordId = $request->msg['recordId'];

        // Check idempotency inside transaction
        if ($this->transactionRepository->getByReferenceId($recordId)) {
            return;
        }

        $transaction = DB::transaction(function () use ($recordId) {

            $transactionData = $this->getTransaction($recordId);

            $walletAddress = $this->usdtAddressRepository->getByAddress($transactionData['toAddress']);
            $user = $walletAddress->user;

            $amount = $transactionData['amount'];
            $chain = $transactionData['chain'];

            $usdtNetwork = $this->usdtNetworkRepository->getByIdentifier($chain);

            $fees = $usdtNetwork->fixed_fees + ($amount * $usdtNetwork->percentage_fees / 100);

            $amountAfterFees = $amount - $fees;

            $transaction = $this->walletService->createUsdtTopUpTransaction($user, $amountAfterFees, $recordId, $chain);

            $user->balance = $this->transactionRepository->getUserBalance($user->id);
            $user->save();

            return $transaction;
        });

        // send notification to user
        Notification::send($transaction->user, new UsdtTopUpNotification($transaction));
    }

    private function logWebhook(Request $request): void
    {
        WebhookLog::create([
            'provider' => 'ccpayment',
            'payload' => $request->all(),
        ]);
    }

    public function verifySignature(Request $request): void
    {
        $app_id = $this->credentials->app_id;
        $app_secret = $this->credentials->app_secret;

        $headerSignature = $request->header('Sign');
        $timestamp = $request->header('Timestamp');
        $payload = $request->getContent();

        // Build the string CCPayment signs
        $signText = $app_id . $timestamp . $payload;

        // Generate HMAC-SHA256 signature
        $computedSignature = hash_hmac('sha256', $signText, $app_secret);

        // Compare safely
        if (!hash_equals($computedSignature, $headerSignature)) {
            throw new BadRequestHttpException('Invalid signature');
        }
    }

    public function getTransaction(string $recordId)
    {
        $content = [
            'recordId' => $recordId,
        ];

        $timestamp = time();
        $app_id = $this->credentials->app_id;
        $app_secret = $this->credentials->app_secret;
        $sign_text = $app_id . $timestamp . json_encode($content);

        $server_sign = hash_hmac('sha256', $sign_text, $app_secret);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Appid' => $app_id,
            'Sign' => $server_sign,
            'Timestamp' => $timestamp
        ])->post('https://ccpayment.com/ccpayment/v2/getAppDepositRecord', $content);

        if ($response->json('code') != 10000) {
            Log::error($response->json('msg'));
            throw new ThirdPartyServiceException($response->json('msg'));
        }

        return $response->json('data')['record'];
    }
}
