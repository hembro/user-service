<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use SensitiveParameter;

final readonly class LoginCredentials
{
    public function __construct(
        public string $email,
        #[SensitiveParameter]
        public string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password']
        );
    }
}
