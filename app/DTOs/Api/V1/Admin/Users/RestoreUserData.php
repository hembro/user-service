<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Admin\Users;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Admin\Users\RestoreRequest;
use App\Models\User;

final readonly class RestoreUserData
{
    public function __construct(
        public User $targetUser,
        public User $actor,
        public Systems $system
    ) {}

    public static function fromRequest(RestoreRequest $request, User $targetUser): self
    {
        return new self(
            targetUser: $targetUser,
            actor: $request->user(),
            system: $request->attributes->get('system')
        );
    }
}
