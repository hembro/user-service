<?php

declare(strict_types=1);

namespace App\Commands\Users;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Http\Requests\Api\V1\Users\UpdatePasswordRequest;
use App\Models\User;
use SensitiveParameter;

final readonly class UpdatePasswordCommand
{
    public function __construct(
        public User $user,
        #[SensitiveParameter]
        public string $newPassword,
        public Systems $system,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(UpdatePasswordRequest $request): self
    {
        return new self(
            user: $request->user(),
            newPassword: $request->validated('password'),
            system: $request->attributes->get('system'),
            metadata: RequestMetadata::fromRequest($request),
        );
    }
}
