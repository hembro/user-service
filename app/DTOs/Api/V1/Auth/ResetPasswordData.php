<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use SensitiveParameter;

final readonly class ResetPasswordData
{
    public function __construct(
        public string $email,
        public string $token,
        #[SensitiveParameter]
        public string $password,
        public Systems $system
    ) {}

    public static function fromRequest(ResetPasswordRequest $request): self
    {
        $data = $request->validated();

        return new self(
            email: $data['email'],
            token: $data['token'],
            password: $data['password'],
            system: $request->attributes->get('system'),
        );
    }
}
