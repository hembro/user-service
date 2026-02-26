<?php

declare(strict_types=1);

namespace App\Events\Users;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UserProfileUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly array $changes,
        public readonly Systems $system,
        public readonly RequestMetadata $metadata
    ) {}
}
