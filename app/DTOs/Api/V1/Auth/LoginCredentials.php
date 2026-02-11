<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use SensitiveParameter;

final readonly class LoginCredentials
{
    public function __construct(
        public string $email,
        #[SensitiveParameter]
        public string $password,
        public Systems $system
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        $data = $request->validated();

        return new self(
            email: $data['email'],
            password: $data['password'],
            system: $request->attributes->get('system'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            system: Systems::from($data['system']),
        );
    }
}
