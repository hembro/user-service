<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Enums\Auth\ChallengeType;
use App\Enums\Systems;

final readonly class CachedAuthChallenge
{
    public function __construct(
        public string $userId,
        public ChallengeType $type,
        public string $fingerprint,
        public string $deviceId,
        public Systems $system,
        public ?string $otpHash = null
    ) {}

    public static function fromCache(array $data): self
    {
        return new self(
            userId: (string) $data['user_id'],
            type: ChallengeType::from($data['type']),
            fingerprint: (string) $data['fingerprint'],
            deviceId: (string) $data['device_id'],
            system: Systems::from($data['system']),
            otpHash: $data['otp_hash'] ?? null
        );
    }
}
