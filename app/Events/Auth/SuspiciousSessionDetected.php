<?php

declare(strict_types=1);

namespace App\Events\Auth;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class SuspiciousSessionDetected
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly RequestMetadata $metadata
    ) {}
}
