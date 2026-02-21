<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\SocialProviders;
use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\SocialLoginRequest;

final readonly class SocialLoginDTO
{
    public function __construct(
        public SocialProviders $provider,
        public string $code,
        public Systems $system,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(SocialLoginRequest $request): self
    {
        $data = $request->validated();

        return new self(
            provider: $request->route('provider'),
            code: $data['code'],
            system: $request->attributes->get('system'),
            metadata: RequestMetadata::fromRequest($request),
        );
    }
}
