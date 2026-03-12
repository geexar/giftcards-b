<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\User\ContactChannelService;

class ContactChannelController extends Controller
{
    public function __construct(private ContactChannelService $contactChannelService) {}

    public function __invoke()
    {
        $contactSupport = $this->contactChannelService->getContactSupport();
        $socialMedia = $this->contactChannelService->getSocialMedia();

        return success([
            'contact_support' => $contactSupport,
            'social_media' => $socialMedia
        ]);
    }
}
