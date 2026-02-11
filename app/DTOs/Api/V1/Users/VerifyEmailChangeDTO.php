<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Users;

use App\Http\Requests\Api\V1\Users\VerifyEmailChangeRequest;

final readonly class VerifyEmailChangeDTO
{
    public function __construct(
        public string $token
    ) {}

    public static function fromRequest(VerifyEmailChangeRequest $request): self
    {
        return new self(
            token: $request->validated('token')
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            token: $data['token']
        );
    }
}
