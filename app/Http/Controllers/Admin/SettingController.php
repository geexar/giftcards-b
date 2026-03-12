<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\ContactSupportSettingsRequest;
use App\Http\Requests\Admin\Settings\GetSettingsRequest;
use App\Http\Requests\Admin\Settings\GiftCardSettingsRequest;
use App\Http\Requests\Admin\Settings\InventorySettingsRequest;
use App\Http\Requests\Admin\Settings\MarkupFeeSettingsRequest;
use App\Http\Requests\Admin\Settings\OrderLimitSettingsRequest;
use App\Http\Requests\Admin\Settings\SocialMediaSettingsRequest;
use App\Services\Admin\SettingService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SettingController extends Controller implements HasMiddleware
{
    public function __construct(private SettingService $settingService) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:update settings', except: [])
        ];
    }

    public function index(GetSettingsRequest $request)
    {
        $settings = $this->settingService->getSettings($request->group);

        return success($settings);
    }

    public function updateInventory(InventorySettingsRequest $request)
    {
        $this->settingService->updateSettingsGroup('inventory', $request->validated());

        return success(true);
    }

    public function updateOrderLimits(OrderLimitSettingsRequest $request)
    {
        $this->settingService->updateSettingsGroup('order_limits', $request->validated());

        return success(true);
    }

    public function updateMarkupFee(MarkupFeeSettingsRequest $request)
    {
        $this->settingService->updateSettingsGroup('markup_fee', $request->validated());

        return success(true);
    }

    public function updateGiftCards(GiftCardSettingsRequest $request)
    {
        $this->settingService->updateGiftCards($request->validated());

        return success(true);
    }

    public function updateContactSupport(ContactSupportSettingsRequest $request)
    {
        $this->settingService->updateSettingsGroup('contact_support', $request->validated());

        return success(true);
    }

    public function updateSocialMedia(SocialMediaSettingsRequest $request)
    {
        $this->settingService->updateSettingsGroup('social_media', $request->validated());

        return success(true);
    }
}
