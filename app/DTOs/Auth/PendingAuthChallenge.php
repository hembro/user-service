<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Enums\Auth\ChallengeType;
use jeremyaliparo\Foundation\Enums\System;

final readonly class PendingAuthChallenge
{
    public function __construct(
        public string $userId,
        public string $challengeId,
        public string $deviceId,
        public ChallengeType $type,
        public System $system,
        public ?string $otpCode
    ) {}
}
