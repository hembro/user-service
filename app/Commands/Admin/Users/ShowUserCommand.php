<?php

declare(strict_types=1);

namespace App\Commands\Admin\Users;

use App\Http\Requests\Api\V1\Admin\Users\ShowRequest;
use App\Models\User;
use jeremyaliparo\Foundation\Enums\System;

final readonly class ShowUserCommand
{
    public function __construct(
        public User $user,
        public System $system
    ) {}

    public static function fromRequest(ShowRequest $request, User $user): self
    {
        return new self(
            user: $user,
            system: $request->attributes->get('system')
        );
    }
}
