<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UsdtNetworkRequest;
use App\Http\Resources\Admin\UsdtNetworkResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\UsdtNetworkRepository;
use App\Services\Admin\UsdtNetworkService;
use App\Services\CCPayment\CCPaymentService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UsdtNetworkController extends Controller implements HasMiddleware
{
    public function __construct(
        private UsdtNetworkService $usdtNetworkService,
        private UsdtNetworkRepository $usdtNetworkRepository,
        private CCPaymentService $ccPaymentService
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:update payment methods', except: []),
        ];
    }

    public function index()
    {
        $networks = $this->usdtNetworkRepository->getPaginatedNetworks();

        return success(new BaseCollection($networks, UsdtNetworkResource::class));
    }

    public function store(UsdtNetworkRequest $request)
    {
        $this->usdtNetworkService->create($request->validated());

        return success(true);
    }

    public function show(string $id)
    {
        $network = $this->usdtNetworkService->getNetwork($id);

        return success(UsdtNetworkResource::make($network));
    }

    public function update(UsdtNetworkRequest $request, string $id)
    {
        $this->usdtNetworkService->update($id, $request->validated());

        return success(true);
    }

    public function destroy(string $id)
    {
        $this->usdtNetworkService->delete($id);

        return success(true);
    }

    public function availableNetworks()
    {
        $networks = $this->ccPaymentService->getAvailableNetworks();

        return success($networks);
    }
}
