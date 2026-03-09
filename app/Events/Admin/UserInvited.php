<?php

declare(strict_types=1);

namespace App\Events\Admin;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use jeremyaliparo\Foundation\Enums\System;

final class UserInvited
{
    use Dispatchable;

    public function __construct(
        public readonly User $targetUser,
        public readonly User $actor,
        public readonly ?string $reason,
        public readonly System $system,
        public readonly ?array $systemContext
    ) {}
}
