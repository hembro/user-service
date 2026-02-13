<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\RefreshTokenDTO;
use App\Exceptions\InvalidRefreshTokenException;
use App\Services\Auth\TokenIssuer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Log\LoggerInterface;

final class RefereshUserToken
{
    public function __construct(
        private readonly TokenIssuer $tokenIssuer,
        private readonly LoggerInterface $logger
    ) {}

    public function handle(RefreshTokenDTO $dto)
    {
        try {
            return $this->tokenIssuer->issueRefreshToken(
                refreshToken: $dto->refreshToken,
                system: $dto->system
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
