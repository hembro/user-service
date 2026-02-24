<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use SensitiveParameter;

final readonly class LoginCommand
{
    public function __construct(
        public string $deviceId,
        public string $email,
        #[SensitiveParameter]
        public string $password,
        public Systems $system,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(LoginRequest $request, string $deviceId): self
    {
        $data = $request->validated();

        return new self(
            deviceId: $deviceId,
            email: $data['email'],
            password: $data['password'],
            system: $request->attributes->get('system'),
            metadata: RequestMetadata::fromRequest($request),
        );
    }
}
