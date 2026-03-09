<?php

declare(strict_types=1);

namespace App\Events\Users;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use jeremyaliparo\Foundation\Enums\System;

final class UserProfileUpdated
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly array $changes,
        public readonly System $system,
        public readonly ?array $systemContext = [],
    ) {}
}
