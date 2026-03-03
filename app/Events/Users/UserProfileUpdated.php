<?php

declare(strict_types=1);

namespace App\Events\Users;

use App\Enums\Systems;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;

final class UserProfileUpdated
{
    use Dispatchable;

    public function __construct(
        public readonly User $user,
        public readonly array $changes,
        public readonly Systems $system,
        public readonly ?array $systemContext = [],
    ) {}
}
