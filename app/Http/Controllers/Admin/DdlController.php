<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\UsdtNetworkBasicResource;
use App\Http\Resources\Admin\CategoryBasicResource;
use App\Http\Resources\Admin\PaymentMethodBasicResource;
use App\Http\Resources\Admin\RoleBasicResource;
use App\Http\Resources\CountryBasicResource;
use App\Repositories\CategoryRepository;
use App\Repositories\CountryRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UsdtNetworkRepository;
use Illuminate\Http\Request;

class DdlController extends Controller
{
    public function __construct(
        private RoleRepository $roleRepository,
        private CountryRepository $countryRepository,
        private CategoryRepository $categoryRepository,
        private PaymentMethodRepository $paymentMethodRepository,
        private UsdtNetworkRepository $usdtNetworkRepository
    ) {}

    public function roles(Request $request)
    {
        $roles = $this->roleRepository->getDdl($request->with_permissions);

        return success(RoleBasicResource::collection($roles));
    }

    public function countries()
    {
        $countries = $this->countryRepository->getDdl();

        return success(CountryBasicResource::collection($countries));
    }

    public function categories(Request $request)
    {
        $categories = $this->categoryRepository->getDdl($request->type, $request->parent_id, $request->only_active);

        return success(CategoryBasicResource::collection($categories));
    }

    public function paymentMethods()
    {
        $paymentMethods = $this->paymentMethodRepository->getAll(request('type'));

        return success(PaymentMethodBasicResource::collection($paymentMethods));
    }

    public function usdtNetworks()
    {
        $usdtNetworks = $this->usdtNetworkRepository->getAll();

        return success(UsdtNetworkBasicResource::collection($usdtNetworks));
    }
}
