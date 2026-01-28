<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Api\V1\Auth\TokenDTO;
use App\Enums\UserStatus;
use App\Events\UserLoggedIn;
use App\Events\UserLoggedOut;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidRefreshTokenException;
use App\Exceptions\UpstreamServiceException;
use App\Models\User;
use Illuminate\Http\Request;
use SensitiveParameter;

use function is_array;

final class AuthService
{
    public function login(
        string $email,
        #[SensitiveParameter]
        string $password,
        string $ip,
        string $userAgent,
        string $timestamp,
        string $system
    ): TokenDTO {
        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user || $user->status !== UserStatus::ACTIVE) {
            throw new InvalidCredentialsException(
                message: 'Invalid credentials.'
            );
        }

        $token = $this->proxyPasswordGrant(
            email: $email,
            password: $password,
            system: $system
        );

        UserLoggedIn::dispatch($user, $ip, $userAgent, $timestamp);

        return $token;
    }

    public function proxyPasswordGrant(
        string $email,
        #[SensitiveParameter]
        string $password,
        string $system
    ): TokenDTO {
        $response = $this->makeInternalRequest(
            parameters: [
                'grant_type' => 'password',
                'username' => $email,
                'password' => $password,
            ],
            headers: [
                'HTTP_X-Source-System' => $system,
            ]
        );

        return TokenDTO::fromArray($response);
    }

    public function refresh(string $refreshToken, string $system): TokenDTO
    {
        try {
            return $this->proxyRefreshTokenGrant(
                refreshToken: $refreshToken,
                system: $system
            );
        } catch (InvalidCredentialsException $e) {
            throw new InvalidRefreshTokenException(
                message: 'Invalid or expired refresh token.',
                previous: $e
            );
        }
    }

    public function proxyRefreshTokenGrant(string $refreshToken, string $system): TokenDTO
    {
        $response = $this->makeInternalRequest(
            parameters: [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ],
            headers: [
                'HTTP_X-Source-System' => $system,
            ]
        );

        return TokenDTO::fromArray($response);
    }

    public function logout(User $user, string $ip, string $userAgent, string $timestamp): void
    {
        $accessToken = $user->token();

        if (! $accessToken) {
            return;
        }

        // @phpstan-ignore-next-line
        $accessToken->revoke();
        // @phpstan-ignore-next-line
        $accessToken->refreshToken?->revoke();

        UserLoggedOut::dispatch($user, $ip, $userAgent, $timestamp);
    }

    private function makeInternalRequest(array $parameters, array $headers = []): array
    {
        $parameters['client_id'] = config('services.passport.password_client_id');
        $parameters['client_secret'] = config('services.passport.password_client_secret');
        $parameters['scope'] = '*'; // Token scopes are for clients only, not for users.

        $headers['HTTP_Content-Type'] = 'application/x-www-form-urlencoded';
        $headers['HTTP_Accept'] = 'application/json';

        $request = Request::create(
            uri: route(
                name: 'api.v1.auth.oauth.token',
                absolute: false
            ),
            method: 'POST',
            parameters: $parameters,
            server: $headers
        );

        $response = app()->handle($request);

        $result = json_decode(
            json: $response->getContent(),
            associative: true
        );

        if (! is_array($result) || $response->isServerError()) {
            throw new UpstreamServiceException(
                message: 'The authentication server is currently unavailable. Please try again later.'
            );
        }

        if (! $response->isSuccessful()) {
            throw new InvalidCredentialsException(
                message: 'Invalid credentials.'
            );
        }

        return $result;
    }
}
