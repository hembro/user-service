<?php

declare(strict_types=1);

namespace App\Events\Admin;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UserDeleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly string $userId,
        public readonly string $userName,
        public readonly string $userEmail,
        public readonly User $actor,
        public readonly Systems $system,
        public readonly RequestMetadata $metadata
    ) {}
}
