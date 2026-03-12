<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\ConfirmOtpRequest;
use App\Http\Requests\User\ProfileRequest;
use App\Http\Requests\Admin\UpdateAppLocaleRequest;
use App\Http\Requests\User\AddPasswordRequest;
use App\Http\Requests\User\UpdateEmailRequest;
use App\Http\Requests\User\UpdateImageRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Resources\User\ProfileResource;
use App\Services\User\ProfileService;

class ProfileController extends Controller
{
    public function __construct(private ProfileService $profileService) {}

    public function index()
    {
        $user = auth('user')->user();

        return success(ProfileResource::make($user));
    }

    public function update(ProfileRequest $request)
    {
        $this->profileService->update($request->validated());

        return success(ProfileResource::make(auth('user')->user()));
    }

    public function updateAppLocale(UpdateAppLocaleRequest $request)
    {
        $this->profileService->updateAppLocale($request->app_locale);

        return success(true);
    }

    public function updateEmail(UpdateEmailRequest $request)
    {
        $this->profileService->updateEmail($request->validated());

        return success(ProfileResource::make(auth('user')->user()));
    }

    public function updateImage(UpdateImageRequest $request)
    {
        $this->profileService->updateImage($request->image);

        return success(true);
    }

    public function addPassword(AddPasswordRequest $request)
    {
        $this->profileService->addPassword($request->password);

        return success(true);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $this->profileService->updatePassword($request->password);

        return success(true);
    }
}
