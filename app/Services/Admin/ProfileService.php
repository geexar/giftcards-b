<?php

namespace App\Services\Admin;

use App\Repositories\AdminRepository;
use Illuminate\Support\Facades\DB;

class ProfileService
{
    public function __construct(private AdminRepository $adminRepository) {}

    public function update(array $data)
    {
        $admin = auth('admin')->user();

        if (isset($data['phone'])) {
            $data['phone'] = normalizePhoneNumber($data['phone']);
        }

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        DB::transaction(function () use ($admin, $data) {
            $this->adminRepository->update($admin, $data);

            if (isset($data['image'])) {
                $admin->clearMediaCollection();
                $admin->addMedia($data['image'])->toMediaCollection();
            }
        });
    }

    public function updateAppLocale(string $appLocale)
    {
        $admin = auth('admin')->user();

        $admin->update(['app_locale' => $appLocale]);
    }
}
