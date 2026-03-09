<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Http\Requests\Api\V1\Auth\ResendVerifyEmailRequest;
use jeremyaliparo\Foundation\Enums\System;

final readonly class ResendVerifyEmailCommand
{
    public function __construct(
        public string $email,
        public System $system
    ) {}

    public static function fromRequest(ResendVerifyEmailRequest $request): self
    {
        return new self(
            email: $request->validated('email'),
            system: $request->attributes->get('system')
        );
    }
}
