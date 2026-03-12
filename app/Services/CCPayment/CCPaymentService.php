<?php

namespace App\Services\CCPayment;

use App\Exceptions\ThirdPartyServiceException;
use App\Repositories\PaymentMethodRepository;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Spatie\MediaLibrary\HasMedia;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CCPaymentService
{
    protected object $credentials;

    public function __construct(private PaymentMethodRepository $paymentMethodRepository)
    {
        $credentials = $this->paymentMethodRepository->getCCPayment()->activeCredentials;

        if (!$credentials) {
            throw new BadRequestHttpException('must setup ccpayment credentials');
        }

        $this->credentials = (object) $credentials->data;
    }

    public function generateWalletAddress(string $network, string $referenceId)
    {
        $content = [
            'referenceId' => "{$referenceId}-{$network}",
            'chain' => $network
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
        ])->post('https://ccpayment.com/ccpayment/v2/getOrCreateAppDepositAddress', $content);

        if ($response->json('code') != 10000) {
            Log::error($response->json('msg'));
            throw new ThirdPartyServiceException($response->json('msg'));
        }

        return $response->json('data')['address'];
    }

    public function getAvailableNetworks()
    {
        $content = [];
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
        ])->post('https://ccpayment.com/ccpayment/v2/getCoinList', $content);

        if ($response->json('code') != 10000) {
            Log::error($response->json('msg'));
            throw new ThirdPartyServiceException($response->json('msg'));
        }

        $availableNetworks = [];

        foreach ($response['data']['coins'] as $coin) {
            if ($coin['symbol'] == 'USDT') {
                foreach ($coin['networks'] as $value) {
                    $availableNetworks[] = [
                        'name' => $value['chainFullName'],
                        'identifier' => $value['chain']
                    ];
                }
                break;
            }
        }

        return $availableNetworks;
    }

    public function generateQrCode(HasMedia $model, string $address): void
    {
        // Generate the QR code as binary PNG (no file saved manually)
        $qrBinary = QrCode::format('png')
            ->size(300)
            ->margin(2)
            ->gradient(0, 0, 0, 199, 144, 42, 'diagonal')
            ->generate($address);

        // Create a temporary file to attach it
        $tempPath = tempnam(sys_get_temp_dir(), 'qr_');
        file_put_contents($tempPath, $qrBinary);

        // Attach QR to the model’s media library (e.g., collection 'qr_codes')
        $model->addMedia($tempPath)
            ->usingName("QR for {$address}")
            ->usingFileName("{$address}.png")
            ->toMediaCollection('qr_codes');

        // Optionally delete the temporary file
        @unlink($tempPath);
    }
}
