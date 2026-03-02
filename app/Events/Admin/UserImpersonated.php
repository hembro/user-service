<?php

declare(strict_types=1);

namespace App\Events\Admin;

use App\Enums\Systems;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UserImpersonated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly User $targetUser,
        public readonly User $actor,
        public readonly Systems $system,
        public readonly ?string $reason
    ) {}
}
