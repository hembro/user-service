<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use SensitiveParameter;

final readonly class ResetPasswordDTO
{
    public function __construct(
        public string $email,
        public string $token,
        #[SensitiveParameter]
        public string $password
    ) {}

    public static function fromRequest(ResetPasswordRequest $request): self
    {
        $data = $request->validated();

        return new self(
            email: $data['email'],
            token: $data['token'],
            password: $data['password'],
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            token: $data['token'],
            password: $data['password'],
        );
    }
}
