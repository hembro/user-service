<?php

declare(strict_types=1);

namespace App\Commands\Admin\Auth;

use App\Http\Requests\Api\V1\Admin\Users\ImpersonateUserRequest;
use App\Models\User;
use jeremyaliparo\Foundation\Enums\System;

final readonly class ImpersonateUserCommand
{
    public function __construct(
        public User $targetUser,
        public User $actor,
        public System $system,
        public ?string $reason
    ) {}

    public static function fromRequest(ImpersonateUserRequest $request, User $targetUser): self
    {
        return new self(
            targetUser: $targetUser,
            actor: $request->user(),
            system: $request->attributes->get('system'),
            reason: $request->validated('reason')
        );
    }
}
