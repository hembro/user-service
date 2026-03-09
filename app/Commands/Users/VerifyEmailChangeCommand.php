<?php

declare(strict_types=1);

namespace App\Commands\Users;

use App\Http\Requests\Api\V1\Users\VerifyEmailChangeRequest;
use App\Models\User;
use jeremyaliparo\Foundation\Enums\System;

final readonly class VerifyEmailChangeCommand
{
    public function __construct(
        public User $user,
        public string $token,
        public System $system
    ) {}

    public static function fromRequest(VerifyEmailChangeRequest $request): self
    {
        return new self(
            user: $request->user(),
            token: $request->validated('token'),
            system: $request->attributes->get('system')
        );
    }
}
