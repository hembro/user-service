<?php

declare(strict_types=1);

namespace App\Events\Users;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use jeremyaliparo\Foundation\Enums\System;

final class UserEmailChangeRequested
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly string $token,
        public readonly string $newEmail,
        public readonly System $system
    ) {}
}
