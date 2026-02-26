<?php

declare(strict_types=1);

namespace App\Events\Auth;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class VerificationLinkRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $verificationUrl,
        public readonly Systems $system,
        public readonly RequestMetadata $metadata
    ) {}
}
