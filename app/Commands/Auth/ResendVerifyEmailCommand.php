<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\ResendVerifyEmailRequest;

final readonly class ResendVerifyEmailCommand
{
    public function __construct(
        public string $email,
        public Systems $system,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(ResendVerifyEmailRequest $request): self
    {
        return new self(
            email: $request->validated('email'),
            system: $request->attributes->get('system'),
            metadata: RequestMetadata::fromRequest($request),
        );
    }
}
