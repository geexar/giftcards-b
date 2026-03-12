<?php

namespace App\Rules;

use App\Models\Admin;
use App\Models\User;
use App\Repositories\AdminRepository;
use App\Repositories\UserRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneExists implements ValidationRule
{
    public function __construct(
        private string $user_type,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $type_repo_map = [
            Admin::class => app(AdminRepository::class),
            User::class => app(UserRepository::class),
        ];

        $userRepository = $type_repo_map[$this->user_type];

        $phone = normalizePhoneNumber($value);

        $user = $userRepository->getByPhone($phone);

        if (!$user) {
            $fail(__('validation.custom.mobile.exists', ['attribute' => __("attributes.$attribute")]));
        }
    }
}
