<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Http\Requests\Api\V1\Auth\DisableTwoFactorRequest;
use App\Models\User;
use jeremyaliparo\Foundation\Enums\System;

final readonly class DisableTwoFactorCommand
{
    public function __construct(
        public User $user,
        public System $system
    ) {}

    public static function fromRequest(DisableTwoFactorRequest $request): self
    {
        return new self(
            user: $request->user(),
            system: $request->attributes->get('system')
        );
    }
}
