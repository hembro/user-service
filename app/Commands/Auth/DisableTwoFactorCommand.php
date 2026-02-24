<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\DisableTwoFactorRequest;
use App\Models\User;

final readonly class DisableTwoFactorCommand
{
    public function __construct(
        public User $user,
        public Systems $system,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(DisableTwoFactorRequest $request): self
    {
        return new self(
            user: $request->user(),
            system: $request->attributes->get('system'),
            metadata: RequestMetadata::fromRequest($request)
        );
    }
}
