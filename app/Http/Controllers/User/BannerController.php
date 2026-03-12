<?php

namespace App\Http\Controllers\User;

use App\Enums\BannerType;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\BannerResource;
use App\Repositories\BannerRepository;

class BannerController extends Controller
{
    public function __construct(private BannerRepository $bannerRepository) {}

    public function __invoke()
    {
        $prmotional = $this->bannerRepository->getActiveBanners(BannerType::PROMOTIONAL);
        $limitedTimeOffers = $this->bannerRepository->getActiveBanners(BannerType::LIMTIED_TIME_OFFER);

        return success([
            'promotional' => BannerResource::collection($prmotional),
            'limited_time_offers' => BannerResource::collection($limitedTimeOffers),
        ]);
    }
}
