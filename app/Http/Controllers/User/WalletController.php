<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UsdtAddressRequest;
use App\Http\Requests\User\WalletTopUpRequest;
use App\Http\Resources\User\UsdtAddressResource;
use App\Http\Resources\User\UsdtNetworkResource;
use App\Repositories\UsdtNetworkRepository;
use App\Services\User\UsdtAddressService;
use App\Services\User\WalletService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        private UsdtNetworkRepository $usdtNetworkRepository,
        private UsdtAddressService $usdtAddressService,
        private WalletService $walletService
    ) {}

    public function balance()
    {
        $balance = auth('user')->user()->balance;

        return success(['balance' => (string) $balance]);
    }

    

    public function getUsdtNetworks()
    {
        $networks = $this->usdtNetworkRepository->getActiveNetworks();

        return success(['networks' => UsdtNetworkResource::collection($networks)]);
    }

    public function getUsdtAddress(UsdtAddressRequest $request)
    {
        $address = $this->usdtAddressService->getAddress(auth('user')->user(), $request->network_id);

        $address = $address ? UsdtAddressResource::make($address) : null;

        return success(['address' => $address]);
    }

    public function createUsdtAddress(UsdtAddressRequest $request)
    {
        $address = $this->usdtAddressService->createAddress(auth('user')->user(), $request->network_id);

        return success(['address' => UsdtAddressResource::make($address)]);
    }

    public function getTopUpPaymentUrl(WalletTopUpRequest $request)
    {
        $url = $this->walletService->getTopUpPaymentUrl($request->amount);

        return success(['url' => $url]);
    }
}
