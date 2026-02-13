<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\AuthenticationOutcomeDTO;
use App\DTOs\Api\V1\Auth\VerifyChallengeDTO;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Auth\ChallengeType;
use App\Exceptions\Auth\InvalidChallengeException;
use App\Models\User;
use App\Services\Auth\DeviceTrustService;
use App\Services\Auth\TokenIssuer;
use Illuminate\Support\Facades\Cache;

final readonly class VerifyAuthenticationChallenge
{
    public function __construct(
        private DeviceTrustService $deviceService,
        private TokenIssuer $tokenIssuer,
    ) {}

    public function handle(VerifyChallengeDTO $dto, RequestMetadata $metadata): AuthenticationOutcomeDTO
    {
        $cacheKey = "auth:challenge:{$dto->challengeId}";
        $payload = Cache::get($cacheKey);

        if (! $payload) {
            throw new InvalidChallengeException('Challenge expired or invalid.');
        }

        $inputHash = hash('sha256', $dto->code);
        if (! hash_equals($payload['otp_hash'], $inputHash)) {
            throw new InvalidChallengeException('Invalid verification code.');
        }

        $currentFingerprint = $this->deviceService->generateFingerprint($metadata);
        if (! hash_equals($payload['fingerprint'], $currentFingerprint)) {
            Cache::forget($cacheKey);
            throw new InvalidChallengeException('Device signature mismatch. Please try logging in again.');
        }

        $user = User::query()->findOrFail($payload['user_id']);
        $type = ChallengeType::from($payload['type']);

        if ($type === ChallengeType::DEVICE_VERIFICATION) {
            $this->deviceService->authorizeDevice($user, $payload['device_uuid'], $metadata);
        }

        Cache::forget($cacheKey);

        return AuthenticationOutcomeDTO::authenticated(
            token: $this->tokenIssuer->issueFullToken($user, $dto->system),
            deviceId: $payload['device_uuid'],
        );
    }
}
