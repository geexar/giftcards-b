<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BaseCollection;
use App\Http\Resources\Admin\UsdtAddressResource;
use App\Repositories\UsdtAddressRepository;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UsdtAddressController extends Controller implements HasMiddleware
{
    public function __construct(private readonly UsdtAddressRepository $usdtAddressRepository) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show usdt addresses', only: ['index']),
        ];
    }

    /**
     * List paginated USDT addresses
     */
    public function index()
    {
        $addresses = $this->usdtAddressRepository->getPaginatedAddresses();

        return success(new BaseCollection($addresses, UsdtAddressResource::class));
    }
}
