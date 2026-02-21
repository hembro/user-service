<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\Systems;
use App\Exceptions\Auth\InvalidChallengeException;
use App\Exceptions\InvalidRefreshTokenException;
use App\Services\Auth\TokenIssuer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Log\LoggerInterface;

final class RefereshUserToken
{
    public function __construct(
        private readonly TokenIssuer $tokenIssuer,
        private readonly DeviceTrustVerifier $deviceService,
        private readonly LoggerInterface $logger
    ) {}

    public function handle(?string $refreshToken, ?string $deviceId, Systems $system, RequestMetadata $metadata)
    {
        if (blank($refreshToken)) {
            throw new InvalidRefreshTokenException('Refresh token is required.');
        }

        if (blank($deviceId)) {
            throw new InvalidChallengeException('Device identifier is missing. Please login again.');
        }

        $user = $this->tokenIssuer->resolveUserFromRefreshToken($refreshToken);

        if (! $user) {
            throw new InvalidRefreshTokenException('Invalid token.');
        }

        if (! $this->deviceService->isTrusted($user, $deviceId, $metadata)) {

            $this->logger->warning(
                message: 'refresh token usage blocked by untrusted device',
                context: [
                    'user_id' => $user->id,
                    'device_id' => $deviceId,
                    'ip' => $metadata->ip,
                ]
            );

            throw new InvalidChallengeException('Device mismatch. Please login again.');
        }

        try {
            return $this->tokenIssuer->issueRefreshToken(
                refreshToken: $refreshToken,
                system: $system
            );
        } catch (OAuthServerException $e) {
            $this->logger->error('OAuth Refresh Failed', ['exception' => $e]);
            throw new InvalidRefreshTokenException(
                message: 'The refresh token is invalid or has expired.',
                previous: $e
            );
        }
    }
}
