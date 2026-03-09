<?php

declare(strict_types=1);

namespace App\Commands\Admin\Users;

use App\Http\Requests\Api\V1\Admin\Users\ResetPasswordRequest;
use App\Models\User;
use jeremyaliparo\Foundation\Enums\System;
use SensitiveParameter;

final readonly class ResetPasswordCommand
{
    public function __construct(
        #[SensitiveParameter]
        public string $password,
        public User $targetUser,
        public User $actor,
        public System $system
    ) {}

    public static function fromRequest(ResetPasswordRequest $request, User $targetUser): self
    {
        return new self(
            password: $request->validated('password'),
            targetUser: $targetUser,
            actor: $request->user(),
            system: $request->attributes->get('system')
        );
    }
}
