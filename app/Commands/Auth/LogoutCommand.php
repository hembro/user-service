<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use jeremyaliparo\Foundation\Enums\System;

final readonly class LogoutCommand
{
    public function __construct(
        public User $user,
        public string $deviceId,
        public System $system
    ) {}

    public static function fromRequest(Request $request, string $deviceId): self
    {
        return new self(
            user: $request->user(),
            deviceId: $deviceId,
            system: $request->attributes->get('system')
        );
    }
}
