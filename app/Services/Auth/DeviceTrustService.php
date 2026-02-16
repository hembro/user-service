<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Models\User;
use App\Models\UserDevice;

final readonly class DeviceTrustService implements DeviceTrustVerifier
{
    public function __construct(
        private ChallengeService $challengeService
    ) {}

    public function isTrusted(User $user, string $deviceId, RequestMetadata $metadata): bool
    {
        // Check strict existence in DB for this user.
        /** @var UserDevice|null */
        $device = $user->devices()
            ->where('device_id', $deviceId)
            ->whereNotNull('verified_at')
            ->first();

        if (! $device) {
            return false;
        }

        // We use the fingerprint to ensure the device config hasn't drastically changed (e.g., session hijacking)
        return hash_equals(
            known_string: $device->fingerprint_hash,
            user_string: $this->challengeService->generateFingerprint($metadata)
        );
    }

    public function trustDevice(User $user, string $deviceId, RequestMetadata $metadata): void
    {
        $user->devices()->updateOrCreate(
            attributes: ['device_id' => $deviceId],
            values: [
                'fingerprint_hash' => $this->challengeService->generateFingerprint($metadata),
                'name' => $metadata->clientType . ' on ' . $metadata->userAgent,
                'last_ip' => $metadata->ip,
                'last_used_at' => now(),
                'verified_at' => now(),
            ]
        );
    }
}
