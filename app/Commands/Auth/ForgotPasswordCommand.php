<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;

final readonly class ForgotPasswordCommand
{
    public function __construct(
        public string $email,
        public Systems $system
    ) {}

    public static function fromRequest(ForgotPasswordRequest $request): self
    {
        return new self(
            email: $request->validated('email'),
            system: $request->attributes->get('system')
        );
    }
}
