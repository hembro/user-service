<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\SocialProviders;
use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\SocialLoginRequest;
use Illuminate\Support\Str;

final readonly class SocialLoginDTO
{
    public function __construct(
        public SocialProviders $provider,
        public string $code,
        public string $deviceId,
        public Systems $system,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(SocialLoginRequest $request): self
    {
        $data = $request->validated();

        return new self(
            provider: $request->route('provider'),
            code: $data['code'],
            deviceId: $request->cookie(config('cookie.device_id.name'))
                ?? $data['device_id']
                ?? Str::uuid()->toString(),
            system: $request->attributes->get('system'),
            metadata: RequestMetadata::fromRequest($request),
        );
    }
}
