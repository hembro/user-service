<?php

declare(strict_types=1);

namespace App\OAuth\Grants;

use DateInterval;
use Illuminate\Support\Facades\Config;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractInternalGrant extends AbstractGrant
{
    public function __construct(RefreshTokenRepositoryInterface $refreshTokenRepository)
    {
        $this->setRefreshTokenRepository($refreshTokenRepository);
        $this->refreshTokenTTL = new DateInterval('P1M');
    }

    abstract protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client): \League\OAuth2\Server\Entities\UserEntityInterface;

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        \League\OAuth2\Server\ResponseTypes\ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ): \League\OAuth2\Server\ResponseTypes\ResponseTypeInterface {

        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));
        $user = $this->validateUser($request, $client);

        $scopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client, $user->getIdentifier());

        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $user->getIdentifier(), $scopes);
        $refreshToken = $this->issueRefreshToken($accessToken);

        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }

    protected function validateClient(ServerRequestInterface $request): ClientEntityInterface
    {
        [$clientId, $clientSecret] = $this->getClientCredentials($request);
        $client = $this->clientRepository->getClientEntity($clientId);

        if (! $client instanceof ClientEntityInterface || ! $client->isConfidential()) {
            throw OAuthServerException::invalidClient($request);
        }

        if ($this->clientRepository->validateClient($clientId, $clientSecret, 'password') === false) {
            throw OAuthServerException::invalidClient($request);
        }

        return $client;
    }

    protected function ensureInternalSignature(ServerRequestInterface $request): void
    {
        $signature = $this->getRequestParameter('internal_signature', $request);

        if ($signature !== Config::get('app.key')) {
            throw OAuthServerException::invalidCredentials();
        }
    }
}
