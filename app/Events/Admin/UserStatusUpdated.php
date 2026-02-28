<?php

declare(strict_types=1);

namespace App\Events\Admin;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UserStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly User $targetUser,
        public readonly UserStatus $oldStatus,
        public readonly User $actor,
        public readonly Systems $system,
        public readonly RequestMetadata $metadata
    ) {}
}
