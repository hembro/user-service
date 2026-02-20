<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Users\RequestEmailChangeRequest;
use App\Models\User;

final readonly class InitiateEmailChangeData
{
    public function __construct(
        public User $user,
        public string $email,
        public Systems $system
    ) {}

    public static function fromRequest(RequestEmailChangeRequest $request): self
    {
        return new self(
            user: $request->user(),
            email: $request->validated('email'),
            system: $request->attributes->get('system')
        );
    }
}
