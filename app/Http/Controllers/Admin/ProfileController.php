<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProfileRequest;
use App\Http\Requests\Admin\UpdateAppLocaleRequest;
use App\Http\Resources\Admin\ProfileResource;
use App\Services\Admin\ProfileService;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService) {}

    public function index()
    {
        $admin = auth('admin')->user();

        return success(ProfileResource::make($admin));
    }

    public function update(ProfileRequest $request)
    {
        $this->profileService->update($request->validated());

        return success(true);
    }

    public function updateAppLocale(UpdateAppLocaleRequest $request)
    {
        $this->profileService->updateAppLocale($request->app_locale);

        return success(true);
    }
}
