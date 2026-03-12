<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CountryRequest;
use App\Http\Resources\Admin\CountryResource;
use App\Http\Resources\BaseCollection;
use App\Repositories\CountryRepository;
use App\Services\Admin\CountryService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CountryController extends Controller implements HasMiddleware
{
    public function __construct(
        private CountryService $countryService,
        private CountryRepository $countryRepository
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:show countries', only: ['index']),
            new Middleware('can:update country', only: ['show', 'update', 'toggleStatus']),
        ];
    }

    /**
     * List all countries
     */
    public function index()
    {
        $countries = $this->countryRepository->getPaginatedCountries();

        return success(new BaseCollection($countries, CountryResource::class));
    }

    /**
     * Show a specific country
     */
    public function show(string $id)
    {
        $country = $this->countryService->getCountry($id);

        return success(CountryResource::make($country));
    }

    /**
     * Update a country
     */
    public function update(CountryRequest $request, string $id)
    {
        $this->countryService->update($id, $request->validated());

        return success(true);
    }

    public function toggleStatus(string $id)
    {
        $this->countryService->toggleStatus($id);

        return success(true);
    }
}
