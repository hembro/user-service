<?php

declare(strict_types=1);

namespace App\Contracts\Auth;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Models\User;

interface DeviceTrustVerifier
{
    public function isTrusted(User $user, RequestMetadata $metadata): bool;

    public function storeChallengeContext(User $user, string $challengeId, string $otpCode, RequestMetadata $metadata): void;

    public function generateFingerprint(RequestMetadata $metadata): string;

    public function authorizeDevice(User $user, string $deviceUuid, RequestMetadata $metadata): void;
}
