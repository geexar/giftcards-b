<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryBasicResource;
use App\Repositories\CountryRepository;

class DdlController extends Controller
{
    public function __construct(
        private CountryRepository $countryRepository
    ) {}

    public function countries()
    {
        $countries = $this->countryRepository->getDdl();

        return success(CountryBasicResource::collection($countries));
    }
}
