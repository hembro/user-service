<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Users\VerifyEmailChangeRequest;
use App\Models\User;

final readonly class VerifyEmailChangeData
{
    public function __construct(
        public User $user,
        public string $token,
        public Systems $system
    ) {}

    public static function fromRequest(VerifyEmailChangeRequest $request): self
    {
        return new self(
            user: $request->user(),
            token: $request->validated('token'),
            system: $request->attributes->get('system')
        );
    }
}
