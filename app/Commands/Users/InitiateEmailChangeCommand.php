<?php

declare(strict_types=1);

namespace App\Commands\Users;

use App\Http\Requests\Api\V1\Users\RequestEmailChangeRequest;
use App\Models\User;
use jeremyaliparo\Foundation\Enums\System;

final readonly class InitiateEmailChangeCommand
{
    public function __construct(
        public User $user,
        public string $email,
        public System $system
    ) {}

    public static function fromRequest(RequestEmailChangeRequest $request): self
    {
        return new self(
            user: $request->user(),
            email: $request->validated('email'),
            system: $request->attributes->get('system')
        );
    }
}
