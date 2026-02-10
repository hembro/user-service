<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Users\RequestEmailChangeRequest;

final readonly class InitiateEmailChangeDTO
{
    public function __construct(
        public string $email,
        public Systems $system
    ) {}

    public static function fromRequest(RequestEmailChangeRequest $request): self
    {
        return new self(
            email: $request->validated('email'),
            system: $request->attributes->get('system')
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            system: $data['system']
        );
    }
}
