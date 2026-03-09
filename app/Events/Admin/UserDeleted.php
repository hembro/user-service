<?php

declare(strict_types=1);

namespace App\Events\Admin;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use jeremyaliparo\Foundation\Enums\System;

final class UserDeleted
{
    use Dispatchable;

    public function __construct(
        public readonly string $userId,
        public readonly User $actor,
        public readonly System $system
    ) {}
}
