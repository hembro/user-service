<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;

final readonly class ForgotPasswordDTO
{
    public function __construct(
        public string $email
    ) {}

    public static function fromRequest(ForgotPasswordRequest $request): self
    {
        return new self(
            email: $request->validated('email')
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email']
        );
    }
}
