<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Enums\Systems;
use App\Http\Requests\Api\V1\Auth\VerifyChallengeRequest;

final class VerifyChallengeDTO
{
    public function __construct(
        public string $challengeId,
        public string $code,
        public Systems $system
    ) {}

    public static function fromRequest(VerifyChallengeRequest $request): self
    {
        $data = $request->validated();

        return new self(
            challengeId: $data['challenge_token'],
            code: $data['code'],
            system: $request->attributes->get('system'),
        );
    }
}
