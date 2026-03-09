<?php

declare(strict_types=1);

namespace App\Commands\Admin\Users;

use App\Http\Requests\Api\V1\Admin\Users\UpdateStatusRequest;
use App\Models\User;
use jeremyaliparo\Foundation\Enums\System;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;

final readonly class UpdateUserStatusCommand
{
    public function __construct(
        public UserStatus $status,
        public User $targetUser,
        public User $actor,
        public System $system
    ) {}

    public static function fromRequest(UpdateStatusRequest $request, User $targetUser): self
    {
        return new self(
            status: $request->enum('status', UserStatus::class),
            targetUser: $targetUser,
            actor: $request->user(),
            system: $request->attributes->get('system')
        );
    }
}
