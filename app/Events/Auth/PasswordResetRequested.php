<?php

declare(strict_types=1);

namespace App\Events\Auth;

use App\Enums\Systems;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

final class PasswordResetRequested
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly string $token,
        public readonly Systems $system
    ) {}
}
