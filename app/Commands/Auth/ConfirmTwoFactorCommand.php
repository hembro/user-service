<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Http\Requests\Api\V1\Auth\ConfirmTwoFactorRequest;
use App\Models\User;
use jeremyaliparo\Foundation\Enums\System;

final readonly class ConfirmTwoFactorCommand
{
    public function __construct(
        public User $user,
        public string $code,
        public System $system
    ) {}

    public static function fromRequest(ConfirmTwoFactorRequest $request): self
    {
        return new self(
            user: $request->user(),
            code: $request->validated('code'),
            system: $request->attributes->get('system')
        );
    }
}
