<?php

declare(strict_types=1);

namespace App\Commands\Auth;

use App\Http\Requests\Api\V1\Auth\VerifyChallengeRequest;
use jeremyaliparo\Foundation\Enums\System;

final class VerifyChallengeCommand
{
    public function __construct(
        public string $challengeId,
        public string $code,
        public System $system
    ) {}

    public static function fromRequest(VerifyChallengeRequest $request): self
    {
        $data = $request->validated();

        return new self(
            challengeId: $data['challenge_id'],
            code: $data['code'],
            system: $request->attributes->get('system')
        );
    }
}
