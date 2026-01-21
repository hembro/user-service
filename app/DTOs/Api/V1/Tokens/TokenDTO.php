<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Tokens;

final readonly class TokenDTO
{
    public function __construct(
        public string $tokenType,
        public string $accessToken,
        public string $refreshToken,
        public int $expiresIn
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tokenType: $data['token_type'],
            accessToken: $data['access_token'],
            refreshToken: $data['refresh_token'],
            expiresIn: $data['expires_in']
        );
    }
}
