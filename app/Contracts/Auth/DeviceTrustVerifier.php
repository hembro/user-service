<?php

declare(strict_types=1);

namespace App\Contracts\Auth;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Models\User;

interface DeviceTrustVerifier
{
    public function isTrusted(User $user, string $deviceId, string $fingerprint): bool;

    public function trustDevice(User $user, string $deviceId, RequestMetadata $metadata): void;
}
