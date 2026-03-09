<?php

declare(strict_types=1);

namespace App\Events\Users;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use jeremyaliparo\Foundation\Enums\System;

final class UserRegistered
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly System $system,
        public readonly ?string $verificationUrl = null,
        public readonly ?array $systemContext = [],
    ) {}
}
