<?php

declare(strict_types=1);

namespace App\DTOs\Api\V1\Auth;

use App\Enums\Auth\AuthResultStatus;
use App\Enums\Auth\ChallengeType;
use InvalidArgumentException;

final readonly class AuthenticationOutcomeDTO
{
    public function __construct(
        public AuthResultStatus $status,
        public ?TokenDTO $token = null,
        public ?string $challengeId = null,
        public ?ChallengeType $challengeType = null,
        public ?string $deviceId = null
    ) {
        // Enforce Integrity: If Authenticated, Token is MANDATORY
        if ($this->status === AuthResultStatus::AUTHENTICATED && $this->token === null) {
            throw new InvalidArgumentException('Cannot create Authenticated outcome without a TokenDTO.');
        }

        // Enforce Integrity: If Challenge, ID and Type are MANDATORY
        if ($this->status === AuthResultStatus::REQUIRES_CHALLENGE) {
            if ($this->challengeId === null || $this->challengeType === null) {
                throw new InvalidArgumentException('Challenge outcome requires ID and Type.');
            }
        }
    }

    public static function authenticated(TokenDTO $token, ?string $deviceId = null): self
    {
        return new self(
            status: AuthResultStatus::AUTHENTICATED,
            token: $token,
            deviceId: $deviceId
        );
    }

    public static function challenge(string $challengeId, ChallengeType $challengeType): self
    {
        return new self(
            status: AuthResultStatus::REQUIRES_CHALLENGE,
            challengeId: $challengeId,
            challengeType: $challengeType
        );
    }
}
