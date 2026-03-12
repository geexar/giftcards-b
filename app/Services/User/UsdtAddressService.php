<?php

namespace App\Services\User;

use App\Models\User;
use App\Repositories\UsdtAddressRepository;
use App\Repositories\UsdtNetworkRepository;
use App\Services\CCPayment\CCPaymentService;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UsdtAddressService
{
    public function __construct(
        private UsdtAddressRepository $usdtAddressRepository,
        private UsdtNetworkRepository $usdtNetworkRepository,
        private CCPaymentService $ccpaymentService
    ) {}

    public function getAddress(User $user, string $networkId)
    {
        $network = $this->usdtNetworkRepository->getById($networkId);

        return $this->usdtAddressRepository->getAddress($user->id, $network->id);
    }

    public function createAddress(User $user, string $networkId)
    {
        $network = $this->usdtNetworkRepository->getById($networkId);
        $address = $this->usdtAddressRepository->getAddress($user->id, $network->id);

        if ($address) {
            throw new BadRequestHttpException('already have address for this network');
        }

        $address = DB::transaction(function () use ($user, $network) {
            $ccpAddress = $this->ccpaymentService->generateWalletAddress($network->identifier, $user->uuid);

            $address = $this->usdtAddressRepository->create([
                'user_id' => $user->id,
                'network_id' => $network->id,
                'address' => $ccpAddress
            ]);

            $this->ccpaymentService->generateQrCode($address, $ccpAddress);

            return $address;
        });

        return $address;
    }
}
