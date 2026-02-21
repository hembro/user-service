<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Http\Request;

final readonly class LogoutCommand
{
    public function __construct(
        public User $user,
        public string $deviceId,
        public Systems $system,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(Request $request, string $deviceId): self
    {
        return new self(
            user: $request->user(),
            deviceId: $deviceId,
            system: $request->attributes->get('system'),
            metadata: RequestMetadata::fromRequest($request)
        );
    }
}
