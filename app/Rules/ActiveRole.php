<?php

namespace App\Rules;

use App\Repositories\RoleRepository;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ActiveRole implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $roleRepository = app(RoleRepository::class);

        $role = $roleRepository->getById($value);

        if ($role && ! $role->is_active) {
            $fail('role is disabled');
        }
    }
}
