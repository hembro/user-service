<?php

declare(strict_types=1);

namespace App\Commands\Admin\Users;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Http\Requests\Api\V1\Admin\Users\DeleteRequest;
use App\Models\User;

final readonly class DeleteUserCommand
{
    public function __construct(
        public User $targetUser,
        public User $actor,
        public Systems $system,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(DeleteRequest $request, User $targetUser): self
    {
        return new self(
            targetUser: $targetUser,
            actor: $request->user(),
            system: $request->attributes->get('system'),
            metadata: RequestMetadata::fromRequest($request),
        );
    }
}
