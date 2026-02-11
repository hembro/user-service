<?php

declare(strict_types=1);

namespace App\Rules;

use App\Enums\Roles;
use App\Enums\Systems;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

final class EnsureRoleBelongsToSystem implements ValidationRule
{
    public function __construct(
        private readonly Systems $system
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $role = Roles::tryFrom((string) $value);

        if ($role === null) {
            return;
        }

        if ($role->system() !== $this->system) {
            $fail("The selected role is not valid for the `{$this->system->value}` system.");
        }
    }
}
