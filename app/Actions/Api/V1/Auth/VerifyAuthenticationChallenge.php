<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Api\V1\Auth\AuthChallengePayloadDTO;
use App\DTOs\Api\V1\Auth\AuthenticationOutcomeDTO;
use App\DTOs\Api\V1\Auth\VerifyChallengeDTO;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Auth\ChallengeType;
use App\Exceptions\Auth\InvalidChallengeException;
use App\Models\User;
use App\Services\Auth\ChallengeService;
use App\Services\Auth\TokenIssuer;
use App\Services\Auth\TwoFactorService;

final readonly class VerifyAuthenticationChallenge
{
    public function __construct(
        private TokenIssuer $tokenIssuer,
        private DeviceTrustVerifier $deviceService,
        private TwoFactorService $twoFactorService,
        private ChallengeService $challengeService
    ) {}

    public function handle(VerifyChallengeDTO $verifyChallengedto, RequestMetadata $metadata): AuthenticationOutcomeDTO
    {
        $cachedData = $this->challengeService->retrieve($verifyChallengedto->challengeId);

        if ($cachedData === null) {
            throw new InvalidChallengeException('Challenge expired or invalid.');
        }

        $payloadDto = AuthChallengePayloadDTO::fromArray($cachedData);

        // Make sure the fingerprint of the user device hasn't changed.
        if (! $this->challengeService->validFingerprint($payloadDto->fingerprint, $metadata)) {
            $this->challengeService->forget($verifyChallengedto->challengeId);
            throw new InvalidChallengeException('Security mismatch. Please login again.');
        }

        $this->verifyCode($payloadDto, $verifyChallengedto->code);

        $user = User::query()->findOrFail($payloadDto->userId);

        $this->deviceService->trustDevice(
            user: $user,
            deviceId: $payloadDto->deviceId,
            metadata: $metadata
        );

        $this->challengeService->forget($verifyChallengedto->challengeId);

        return AuthenticationOutcomeDTO::authenticated(
            token: $this->tokenIssuer->issueFullToken($user, $payloadDto->system),
            deviceId: $payloadDto->deviceId,
        );
    }

    private function verifyCode(AuthChallengePayloadDTO $payload, string $inputCode): void
    {
        match ($payload->type) {
            ChallengeType::TWO_FACTOR => $this->verifyTwoFactor($payload->userId, $inputCode),
            ChallengeType::DEVICE_VERIFICATION => $this->verifyDeviceOtp($payload->otpHash, $inputCode),
        };
    }

    private function verifyDeviceOtp(string $otpHash, string $inputCode): void
    {
        if ($otpHash === null) {
            throw new InvalidChallengeException('Invalid challenge state.');
        }

        $inputHash = hash('sha256', $inputCode);

        if (! hash_equals($otpHash, $inputHash)) {
            throw new InvalidChallengeException('Invalid verification code.');
        }
    }

    private function verifyTwoFactor(string $userId, string $inputCode): void
    {
        $user = User::query()->findOrFail($userId);

        if (! $this->twoFactorService->verify($user, $inputCode)) {
            throw new InvalidChallengeException('Invalid two-factor code.');
        }
    }
}
