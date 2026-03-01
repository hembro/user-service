<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Context;

final readonly class DeviceTrustService implements DeviceTrustVerifier
{
    public function __construct(
        private ChallengeService $challengeService
    ) {}

    public function isTrusted(User $user, string $deviceId): bool
    {
        if ($deviceId === null) {
            return false;
        }

        $currentFingerprint = $this->challengeService->generateFingerprint();
        $cacheKey = $this->cacheKey($user->id, $deviceId);
        $cachedHash = Cache::get($cacheKey);

        if ($cachedHash && hash_equals($cachedHash, $currentFingerprint)) {
            return true;
        }

        /** @var UserDevice|null */
        $device = $user->devices()
            ->where('device_id', $deviceId)
            ->whereNotNull('verified_at')
            ->first();

        if (! $device) {
            return false;
        }

        if (! hash_equals($device->fingerprint_hash, $currentFingerprint)) {
            return false;
        }

        Cache::put(
            key: $cacheKey,
            value: $device->fingerprint_hash,
            ttl: now()->addMinutes((int) Config::get('auth.otp.expire', 10))
        );

        return true;
    }

    public function trustDevice(User $user, string $deviceId): void
    {
        $clientType = (string) Context::get('client_type', 'unknown');
        $userAgent = (string) Context::get('user_agent', 'unknown');
        $ipAddress = (string) Context::get('ip_address');

        $user->devices()->updateOrCreate(
            attributes: ['device_id' => $deviceId],
            values: [
                'fingerprint_hash' => $this->challengeService->generateFingerprint(),
                'name' => $clientType . ' on ' . mb_substr($userAgent, 0, 50),
                'last_ip' => $ipAddress,
                'last_used_at' => now(),
                'verified_at' => now(),
            ]
        );
    }

    public function resolveDeviceId(Request $request): ?string
    {
        $headerValue = $request->header('X-Device-UUID');

        if (is_string($headerValue) && ! blank($headerValue)) {
            return $headerValue;
        }

        return $request->cookie((string) Config::get('cookie.device_id.name'));
    }

    public function forgetDevice(User $user, string $deviceId): void
    {
        Cache::forget($this->cacheKey($user->id, $deviceId));
        $user->devices()->where('device_id', $deviceId)->delete();
    }

    private function cacheKey(string $userId, string $deviceId): string
    {
        return "auth:trust:{$userId}:{$deviceId}";
    }
}
