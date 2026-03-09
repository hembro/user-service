<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Enums\SocialProviders;
use App\Http\Requests\Api\V1\Auth\SocialLoginRequest;
use jeremyaliparo\Foundation\Enums\System;

final readonly class SocialLoginCommand
{
    public function __construct(
        public SocialProviders $provider,
        public string $code,
        public string $deviceId,
        public System $system
    ) {}

    public static function fromRequest(SocialLoginRequest $request, string $deviceId, SocialProviders $provider): self
    {
        $data = $request->validated();

        return new self(
            provider: $provider,
            code: $data['code'],
            deviceId: $deviceId,
            system: $request->attributes->get('system')
        );
    }
}
