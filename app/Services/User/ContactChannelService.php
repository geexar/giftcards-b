<?php

namespace App\Services\User;

use App\Repositories\SettingRepository;
use App\Services\Admin\SettingService;

class ContactChannelService
{
    public function __construct(private SettingRepository $settingRepository, private SettingService $settingService) {}

    public function getContactSupport()
    {
        $settings = $this->settingRepository->getSettingsGroup('contact_support');

        return $this->settingService->formatSettings($settings);
    }

    public function getSocialMedia()
    {
        $settings = $this->settingRepository->getSettingsGroup('social_media');

        return $this->settingService->formatSettings($settings);
    }
}
