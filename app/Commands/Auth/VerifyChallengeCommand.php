<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\DTOs\Shared\RequestMetadata;
use App\Http\Requests\Api\V1\Auth\VerifyChallengeRequest;

final class VerifyChallengeCommand
{
    public function __construct(
        public string $challengeId,
        public string $code,
        public RequestMetadata $metadata
    ) {}

    public static function fromRequest(VerifyChallengeRequest $request): self
    {
        $data = $request->validated();

        return new self(
            challengeId: $data['challenge_id'],
            code: $data['code'],
            metadata: RequestMetadata::fromRequest($request),
        );
    }
}
