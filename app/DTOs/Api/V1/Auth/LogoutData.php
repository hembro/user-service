<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Http\Request;

final readonly class LogoutData
{
    public function __construct(
        public User $user,
        public string $deviceId,
        public RequestMetadata $metadata,
        public Systems $system
    ) {}

    public static function fromRequest(Request $request, string $deviceId): self
    {
        return new self(
            user: $request->user(),
            deviceId: $deviceId,
            metadata: RequestMetadata::fromRequest($request),
            system: $request->attributes->get('system'),
        );
    }
}
