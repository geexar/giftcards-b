<?php

namespace App\Services\Lirat;

use App\Repositories\IntegrationRepository;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class LiratGiftCardService
{
    protected object $credentials;

    public function __construct(private IntegrationRepository $integrationRepository)
    {
        $credentials = $this->integrationRepository->getLiratGitCards()->activeCredentials;

        if (!$credentials) {
            throw new BadRequestHttpException('must setup lirat gift card credentials');
        }

        $this->credentials = (object) $credentials->data;
    }

    /*
    |--------------------------------------------------------------------------
    | Public API Methods
    |--------------------------------------------------------------------------
    */

    public function getBalance()
    {
        if (app()->environment('local')) {

            return (float) number_format(2000, 3, '.', '');
        }

        $json = $this->sendRequest(
            'https://taxes.like4app.com/online/check_balance',
            $this->getEssentialKeys()
        );

        return (float) $json->balance;
    }

    public function getCategories(string $locale = 'en')
    {
        $json = $this->sendRequest(
            'https://taxes.like4app.com/online/categories',
            $this->getEssentialKeys($locale)
        );

        return $json->data;
    }

    public function getProducts(string $locale = 'en')
    {
        $json = $this->sendRequest(
            'https://taxes.like4app.com/online/products',
            $this->getEssentialKeys($locale)
        );

        return $json->data;
    }

    public function getProduct(string $id, string $locale = 'en')
    {
        $body = [
            ...$this->getEssentialKeys($locale),
            [
                'name'     => 'ids[]',
                'contents' => $id,
            ],
        ];

        $json = $this->sendRequest(
            'https://taxes.like4app.com/online/products',
            $body
        );

        return (object) $json->data[0];
    }

    public function createOrder(string $productId, string $referenceId)
    {
        if (app()->environment('local')) {
            $fakeOrderId = random_int(100000000, 999999999);
            $fakeSerialId = random_int(100000000, 999999999);
            $fakeSerialCode = base64_encode(Str::random(24));
            $fakeProductName = 'Product ' . Str::upper(Str::random(5));

            $payload = [
                'response' => 1,
                'productName' => $fakeProductName,
                'productImage' => 'https://picsum.photos/400/?random=' . rand(1, 9999),
                'orderId' => $fakeOrderId,
                'referenceId' => $referenceId,
                'orderDate' => now()->format('Y/m/d H:i'),
                'orderPrice' => round(mt_rand(1, 500) / 10, 2),
                'orderPriceWithoutVat' => round(mt_rand(1, 500) / 10, 2),
                'orderCurrency' => 'USD',
                'vatAmount' => 0,
                'vatPercentage' => '0%',
                'serials' => [
                    [
                        'serialId' => (string) $fakeSerialId,
                        'serialCode' => $fakeSerialCode,
                        'serialNumber' => '',
                        'validTo' => now()->addYear()->format('d/m/Y'),
                        'additionalGiftTitle' => '',
                        'additionalGiftSerial' => '',
                    ],
                ],
            ];

            // Return a real Illuminate HTTP Client Response object
            return new \Illuminate\Http\Client\Response(new \GuzzleHttp\Psr7\Response(200, [], json_encode($payload)));
        }

        // Real API request
        $time = time();
        $body = [
            ...$this->getEssentialKeys(),
            ['name' => 'productId', 'contents' => $productId],
            ['name' => 'referenceId', 'contents' => $referenceId],
            ['name' => 'quantity', 'contents' => 1],
            ['name' => 'time', 'contents' => $time],
            ['name' => 'hash', 'contents' => $this->generateHash($time)],
        ];

        $response = Http::asMultipart()->post('https://taxes.like4app.com/online/order', $body);

        return $response;
    }



    /*
    |--------------------------------------------------------------------------
    | Private Helpers
    |--------------------------------------------------------------------------
    */

    private function sendRequest(string $url, array $body): object
    {
        $response = Http::asMultipart()->post($url, $body);

        if (!$response->successful()) {
            throw new Exception($response->body());
        }

        $json = (object) $response->json();

        if ($json->response == 0) {
            throw new Exception($json->message ?? 'Unknown API error');
        }

        return $json;
    }

    private function getEssentialKeys(string $locale = 'en'): array
    {
        return [
            ['name' => 'deviceId',     'contents' => $this->credentials->device_id],
            ['name' => 'email',        'contents' => $this->credentials->email],
            ['name' => 'securityCode', 'contents' => $this->credentials->security_code],
            ['name' => 'langId',       'contents' => $this->getLangId($locale)],
        ];
    }

    private function getLangId(string $locale): int
    {
        if (!in_array($locale, ['en', 'ar'], true)) {
            throw new InvalidArgumentException('invalid locale');
        }

        return $locale === 'en' ? 1 : 2;
    }

    private function generateHash(int $time): string
    {
        $email = strtolower($this->credentials->email);
        $phone = $this->credentials->phone;
        $key   = $this->credentials->hash_key;

        return hash('sha256', $time . $email . $phone . $key);
    }
}
