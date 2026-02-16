<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use Illuminate\Support\Str;
use SensitiveParameter;

final readonly class LoginCredentials
{
    public function __construct(
        public string $email,
        #[SensitiveParameter]
        public string $password,
        public string $deviceId,
        public Systems $system
    ) {}

    public static function fromRequest(LoginRequest $request): self
    {
        $data = $request->validated();

        return new self(
            email: $data['email'],
            password: $data['password'],
            deviceId: $request->cookie(config('cookie.device_id.name'))
                ?? $data['device_id']
                ?? Str::uuid()->toString(),
            system: $request->attributes->get('system'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            deviceId: $data['device_id'],
            system: Systems::from($data['system']),
        );
    }
}
