<?php

namespace App\Rules;

use App\Models\Admin;
use App\Models\User;
use App\Repositories\AdminRepository;
use App\Repositories\UserRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniquePhone implements ValidationRule
{
    public function __construct(
        private ?string $country_code,
        private string $user_type,
        private ?int $id = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->country_code) {
            return;
        }

        $type_repo_map = [
            Admin::class => app(AdminRepository::class),
            User::class => app(UserRepository::class),
        ];

        $userRepository = $type_repo_map[$this->user_type];

        $phone = normalizePhoneNumber($value);

        $user = $userRepository->getByPhone($this->country_code, $phone);

        // In Create Case
        if ($user && !$this->id) {
            $fail(__('validation.custom.phone.unique', ['attribute' => __("attributes.{$attribute}")]));
            return;
        }

        // In Update Case
        if ($user && $user->id != $this->id) {
            $fail(__('validation.custom.phone.unique', ['attribute' => __("attributes.{$attribute}")]));
        }
    }
}
