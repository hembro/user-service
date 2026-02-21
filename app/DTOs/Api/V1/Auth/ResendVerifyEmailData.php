<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\ResendVerifyEmailRequest;

final readonly class ResendVerifyEmailData
{
    public function __construct(
        public string $email,
        public Systems $system
    ) {}

    public static function fromRequest(ResendVerifyEmailRequest $request): self
    {
        return new self(
            email: $request->validated('email'),
            system: $request->attributes->get('system')
        );
    }
}
