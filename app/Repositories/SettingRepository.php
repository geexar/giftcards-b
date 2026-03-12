<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingRepository extends BaseRepository
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all settings (optional, not cached)
     */
    public function getAll()
    {
        return $this->model->get();
    }

    /**
     * Get all settings in a group as models (cached)
     */
    public function getSettingsGroup(string $group)
    {
        return Cache::rememberForever("settings_group:{$group}", function () use ($group) {
            return $this->model->where('group', $group)->get();
        });
    }

    /**
     * Update a single setting and clear cache for the group
     */
    public function updateSetting(string $group, string $key, $value)
    {
        $this->model
            ->where('group', $group)
            ->where('key', $key)
            ->update(['value' => $value]);

        // Clear cached group after update
        Cache::forget("settings_group:{$group}");
    }

    /**
     * Get a single setting's value, using cached group
     */
    public function getSetting(string $group, string $key)
    {
        $settings = $this->getSettingsGroup($group); // cached collection of models

        $setting = $settings->firstWhere('key', $key);

        return $setting;
    }
}
