<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\DisableTwoFactorRequest;
use SensitiveParameter;

final readonly class DisableTwoFactorDTO
{
    public function __construct(
        #[SensitiveParameter]
        public string $password,
        public string $deviceId,
        public Systems $system
    ) {}

    public static function fromRequest(DisableTwoFactorRequest $request): self
    {
        $data = $request->validated();

        return new self(
            password: $data['password'],
            deviceId: $request->cookie(config('cookie.device_id.name')) ?? $data['device_id'],
            system: $request->attributes->get('system'),
        );
    }
}
