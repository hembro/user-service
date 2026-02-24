<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\ConfirmTwoFactorRequest;
use App\Models\User;

final readonly class ConfirmTwoFactorCommand
{
    public function __construct(
        public User $user,
        public string $code,
        public Systems $system,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(ConfirmTwoFactorRequest $request): self
    {
        return new self(
            user: $request->user(),
            code: $request->validated('code'),
            system: $request->attributes->get('system'),
            metadata: RequestMetadata::fromRequest($request),
        );
    }
}
