<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;

final readonly class RefreshTokenDTO
{
    public function __construct(
        public string $refreshToken,
        public Systems $system
    ) {}

    public static function fromRequest(RefreshTokenRequest $request): self
    {
        $data = $request->validated();

        return new self(
            refreshToken: $request->cookie('refresh_token') ?? $data('refresh_token', ''),
            system: $request->attributes->get('system'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            refreshToken: $data['refresh_token'],
            system: Systems::from($data['system']),
        );
    }
}
