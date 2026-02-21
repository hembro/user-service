<?php

declare(strict_types=1);

namespace App\Contracts\Auth;

use App\DTOs\Shared\RequestMetadata;
use App\Models\User;
use Illuminate\Http\Request;

interface DeviceTrustVerifier
{
    public function isTrusted(User $user, string $deviceId, RequestMetadata $metadata): bool;

    public function trustDevice(User $user, string $deviceId, RequestMetadata $metadata): void;

    public function resolveDeviceId(Request $request): ?string;

    public function forgetDevice(User $user, string $deviceId): void;
}
