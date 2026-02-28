<?php

declare(strict_types=1);

namespace App\Commands\Admin\Users;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Http\Requests\Api\V1\Admin\Users\UpdateStatusRequest;
use App\Models\User;

final readonly class UpdateUserStatusCommand
{
    public function __construct(
        public UserStatus $status,
        public User $targetUser,
        public User $actor,
        public Systems $system,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(UpdateStatusRequest $request, User $targetUser): self
    {
        return new self(
            status: $request->enum('status', UserStatus::class),
            targetUser: $targetUser,
            actor: $request->user(),
            system: $request->attributes->get('system'),
            metadata: RequestMetadata::fromRequest($request),
        );
    }
}
