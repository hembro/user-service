<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\DTOs\Shared\RequestMetadata;
use App\Enums\Auth\ChallengeType;
use App\Enums\Systems;
use InvalidArgumentException;

final readonly class AuthChallengePayloadDTO
{
    public function __construct(
        public string $userId,
        public ChallengeType $type,
        public string $fingerprint,
        public string $deviceId,
        public Systems $system,
        public RequestMetadata $metadata,
        public ?string $otpHash
    ) {}

    public static function fromArray(array $data): self
    {
        if (! isset($data['user_id'], $data['type'], $data['fingerprint'], $data['device_id'], $data['system'], $data['metadata'])) {
            throw new InvalidArgumentException('Invalid challenge payload structure.');
        }

        return new self(
            userId: $data['user_id'],
            type: ChallengeType::from($data['type']),
            fingerprint: $data['fingerprint'],
            deviceId: $data['device_id'],
            system: Systems::from($data['system']),
            metadata: RequestMetadata::fromArray($data['metadata']),
            otpHash: $data['otp_hash'] ?? null
        );
    }
}
