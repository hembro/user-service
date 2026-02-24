<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Auth\ChallengeType;
use App\Enums\Systems;

final readonly class PendingAuthChallenge
{
    public function __construct(
        public string $userId,
        public string $challengeId,
        public string $deviceId,
        public ChallengeType $type,
        public Systems $system,
        public RequestMetadata $metadata,
        public ?string $otpCode
    ) {}
}
