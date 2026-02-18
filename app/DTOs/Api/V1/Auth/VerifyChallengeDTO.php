<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Http\Requests\Api\V1\Auth\VerifyChallengeRequest;

final class VerifyChallengeDTO
{
    public function __construct(
        public string $challengeId,
        public string $code,
    ) {}

    public static function fromRequest(VerifyChallengeRequest $request): self
    {
        $data = $request->validated();

        return new self(
            challengeId: $data['challenge_id'],
            code: $data['code'],
        );
    }
}
