<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Api\V1\Auth\LoginCredentials;
use App\DTOs\Api\V1\Auth\TokenDTO;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\UserStatus;
use App\Events\UserLoggedIn;
use App\Events\UserLoggedOut;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidRefreshTokenException;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Response as Psr7Response;
use Nyholm\Psr7\ServerRequest as Psr7Request;
use RuntimeException;

final readonly class AuthService
{
    public function __construct(
        private AuthorizationServer $server,
    ) {}

    public function login(LoginCredentials $credentials, RequestMetadata $metadata): TokenDTO
    {
        $user = User::query()
            ->where('email', $credentials->email)
            ->first();

        if (! $user || $user->status !== UserStatus::ACTIVE) {
            throw new InvalidCredentialsException(
                message: 'Invalid credentials.'
            );
        }

        try {
            $tokenDto = $this->dispatchRequest(
                payload: [
                    'grant_type' => 'password',
                    'username' => $credentials->email,
                    'password' => $credentials->password,
                    'scope' => $user->roles->implode('name', ' '),
                ]
            );
        } catch (OAuthServerException) {
            throw new InvalidCredentialsException(
                message: 'Invalid credentials.'
            );
        }

        UserLoggedIn::dispatch($user, $metadata);

        return $tokenDto;
    }

    public function refresh(string $refreshToken): TokenDTO
    {
        try {
            return $this->dispatchRequest(
                payload: [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'scope' => '',
                ]
            );
        } catch (OAuthServerException $e) {
            throw new InvalidRefreshTokenException(
                message: 'The refresh token is invalid or has expired.',
                previous: $e
            );
        }
    }

    public function logout(User $user, RequestMetadata $metadata): void
    {
        /** @var \Laravel\Passport\Token|null $accessToken */
        $accessToken = $user->token();

        if (! $accessToken) {
            return;
        }

        $accessToken->revoke();
        $accessToken->refreshToken?->revoke();

        UserLoggedOut::dispatch($user, $metadata);
    }

    private function dispatchRequest(array $payload): TokenDTO
    {
        $clientId = Config::get('services.passport.password_client_id');
        $clientSecret = Config::get('services.passport.password_client_secret');

        if (! $clientId || ! $clientSecret) {
            throw new RuntimeException(
                message: 'OAuth Password Client credentials are missing from config.'
            );
        }

        $payload = array_merge($payload, [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        $request = (new Psr7Request('POST', 'oauth/token'))
            ->withParsedBody($payload);

        $response = $this->server->respondToAccessTokenRequest(
            request: $request,
            response: new Psr7Response()
        );

        $data = json_decode((string) $response->getBody(), true);

        return TokenDTO::fromArray($data);
    }
}
