<?php

declare(strict_types=1);

namespace App\Events\Auth;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use jeremyaliparo\Foundation\Enums\System;

final class PasswordResetRequested
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly string $token,
        public readonly System $system
    ) {}
}
