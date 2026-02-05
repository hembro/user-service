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
        private readonly ?string $systemValue
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $system = Systems::tryFrom((string) $this->systemValue);

        if ($system === null) {
            return;
        }

        $role = Roles::tryFrom((string) $value);

        if ($role === null) {
            return;
        }

        if ($role->system() !== $system) {
            $fail("The selected role is not valid for the {$system->value} system.");
        }
    }
}
