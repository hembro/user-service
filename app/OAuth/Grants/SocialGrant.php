<?php

declare(strict_types=1);

namespace App\OAuth\Grants;

use App\Models\User;
use DateInterval;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Bridge\User as PassportUser;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\UserEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SocialGrant extends AbstractGrant
{
    public function __construct(RefreshTokenRepositoryInterface $refreshTokenRepository)
    {
        $this->setRefreshTokenRepository($refreshTokenRepository);
        $this->refreshTokenTTL = new DateInterval('P1M'); // Default TTL
    }

    public function getIdentifier(): string
    {
        return 'social';
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        \League\OAuth2\Server\ResponseTypes\ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ): \League\OAuth2\Server\ResponseTypes\ResponseTypeInterface {

        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));

        // Passing $client here to enforce our internal architectural rules
        $user = $this->validateUser($request, $client);

        $scopes = $this->scopeRepository->finalizeScopes(
            $scopes,
            $this->getIdentifier(),
            $client,
            $user->getIdentifier()
        );

        $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $user->getIdentifier(), $scopes);
        $refreshToken = $this->issueRefreshToken($accessToken);

        $responseType->setAccessToken($accessToken);
        $responseType->setRefreshToken($refreshToken);

        return $responseType;
    }

    protected function validateClient(ServerRequestInterface $request): ClientEntityInterface
    {
        [$clientId, $clientSecret] = $this->getClientCredentials($request);

        if (empty($clientId)) {
            throw OAuthServerException::invalidRequest('client_id');
        }

        $client = $this->clientRepository->getClientEntity($clientId);

        if (! $client instanceof ClientEntityInterface) {
            throw OAuthServerException::invalidClient($request);
        }

        // We temporarily spoof the grant_type as 'password' to validate the client's secret,
        // bypassing the strict grant_type mapping without altering the database schema.
        if ($this->clientRepository->validateClient($clientId, $clientSecret, 'password') === false) {
            throw OAuthServerException::invalidClient($request);
        }

        return $client;
    }

    protected function validateUser(ServerRequestInterface $request, ClientEntityInterface $client): UserEntityInterface
    {
        $userId = $this->getRequestParameter('user_id', $request);
        $provider = $this->getRequestParameter('provider', $request);
        $signature = $this->getRequestParameter('internal_signature', $request);

        if (! $userId || ! $provider) {
            throw OAuthServerException::invalidRequest('user_id or provider missing');
        }

        if (! $client->isConfidential()) {
            throw OAuthServerException::invalidClient($request);
        }

        // --- GATE 1: THE SIGNATURE ---
        if ($signature !== Config::get('app.key')) {
            \Illuminate\Support\Facades\Log::error('OAUTH GATE 1 FAILED: Signature Mismatch', [
                'received' => $signature,
                'expected' => Config::get('app.key'),
            ]);
            throw OAuthServerException::invalidCredentials();
        }

        // --- GATE 2: THE DATABASE ---
        $user = User::query()->find($userId);

        if (! $user) {
            \Illuminate\Support\Facades\Log::error('OAUTH GATE 2 FAILED: User Not Found', [
                'user_id_received' => $userId,
                'type_of_user_id' => gettype($userId),
            ]);
            throw OAuthServerException::invalidCredentials();
        }

        return new PassportUser((string) $user->getAuthIdentifier());
    }
}
