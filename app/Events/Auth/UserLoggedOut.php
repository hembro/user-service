<?php

declare(strict_types=1);

namespace App\Events\Auth;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UserLoggedOut
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public RequestMetadata $metadata
    ) {}
}
