<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Enums\Systems;
use App\Models\User;
use Illuminate\Http\Request;

final readonly class EnableTwoFactorCommand
{
    public function __construct(
        public User $user,
        public Systems $system
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            user: $request->user(),
            system: $request->attributes->get('system')
        );
    }
}
