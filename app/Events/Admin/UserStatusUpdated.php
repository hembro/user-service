<?php

declare(strict_types=1);

namespace App\Events\Admin;

use App\Enums\Systems;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;

final class UserStatusUpdated
{
    use Dispatchable;

    public function __construct(
        public readonly User $targetUser,
        public readonly UserStatus $oldStatus,
        public readonly User $actor,
        public readonly Systems $system
    ) {}
}
