<?php

namespace App\Services\Admin;

use App\Repositories\IntegrationRepository;
use App\Repositories\SettingRepository;
use App\Services\ActivityLogService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SettingService
{
    public function __construct(
        private SettingRepository $settingRepository,
        private IntegrationRepository $integrationRepository,
        private ActivityLogService $activityLogService
    ) {}

    public function getSettings(string $group)
    {
        if ($group == 'gift_cards') {
            return $this->getGiftCardsSettings();
        }

        $settings = $this->settingRepository->getSettingsGroup($group);

        return $this->formatSettings($settings);
    }

    public function getGiftCardsSettings()
    {
        $integration = $this->integrationRepository->getLiratGitCards();
        $config = (object) ($integration->config ?? []);

        $settings = [
            'active_mode' => $integration->active_mode,
            'base_price_source' => $config->base_price_source ?? null,
        ];

        return $settings;
    }

    public function updateSettingsGroup(string $group, array $data)
    {
        DB::transaction(function () use ($group, $data) {
            foreach ($data as $key => $value) {
                $this->settingRepository->updateSetting($group, $key, $value);
            }

            $this->activityLogService->store(null, 'settings.updated');
        });
    }

    public function updateGiftCards(array $data): void
    {
        $integration = $this->integrationRepository->getLiratGitCards();

        // General fields only
        $generalData = Arr::only($data, ['active_mode']);

        // Update config only if base_price_source is sent
        if (isset($data['base_price_source'])) {
            $generalData['config'] = [
                'base_price_source' => $data['base_price_source'],
            ];
        }

        DB::transaction(function () use ($integration, $generalData, $data) {

            // 1. Update integration config
            if (! empty($generalData)) {
                $this->integrationRepository->update($integration, $generalData);
            }

            // 2. Update credentials ONLY if active_mode is sent
            if (isset($data['active_mode'])) {
                $activeMode = $data['active_mode'];

                if (isset($data[$activeMode])) {
                    $integration->credentials()->updateOrCreate(
                        ['mode' => $activeMode],
                        ['data' => $data[$activeMode]]
                    );
                }
            }

            $this->activityLogService->store(null, 'settings.updated');
        });
    }

    public function formatSettings(Collection $settings): array
    {
        return $settings->pluck('value', 'key')->toArray();
    }
}
