<?php

declare(strict_types=1);

namespace App\Events\Admin;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use jeremyaliparo\Foundation\Enums\System;

final class UserImpersonated
{
    use Dispatchable;

    public function __construct(
        public readonly User $targetUser,
        public readonly User $actor,
        public readonly System $system,
        public readonly ?string $reason
    ) {}
}
