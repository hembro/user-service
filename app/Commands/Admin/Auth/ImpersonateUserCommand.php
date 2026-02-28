<?php

declare(strict_types=1);

namespace App\Commands\Admin\Auth;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Http\Requests\Api\V1\Admin\Users\ImpersonateUserRequest;
use App\Models\User;

final readonly class ImpersonateUserCommand
{
    public function __construct(
        public User $targetUser,
        public User $actor,
        public Systems $system,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(ImpersonateUserRequest $request, User $targetUser): self
    {
        return new self(
            targetUser: $targetUser,
            actor: $request->user(),
            system: $request->attributes->get('system'),
            metadata: RequestMetadata::fromRequest($request),
        );
    }
}
