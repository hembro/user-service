<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Api\V1\Tokens\TokenDTO;
use App\Enums\Users\UserStatus;
use App\Events\UserLoggedIn;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidRefreshTokenException;
use App\Exceptions\UpstreamServiceException;
use App\Models\User;
use Illuminate\Http\Request;
use SensitiveParameter;

final class AuthService
{
    public function login(
        string $email,
        #[SensitiveParameter]
        string $password,
        string $ip,
        string $userAgent
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
            password: $password
        );

        event(
            new UserLoggedIn(
                user: $user,
                ip: $ip,
                userAgent: $userAgent
            )
        );

        return $token;
    }

    public function proxyPasswordGrant(
        string $email,
        #[SensitiveParameter]
        string $password
    ): TokenDTO {
        $response = $this->makeInternalRequest(
            parameters: [
                'grant_type' => 'password',
                'username' => $email,
                'password' => $password,
            ]
        );

        return TokenDTO::fromArray($response);
    }

    public function refresh(string $refreshToken): TokenDTO
    {
        try {
            return $this->proxyRefreshTokenGrant(
                refreshToken: $refreshToken
            );
        } catch (InvalidCredentialsException $e) {
            throw new InvalidRefreshTokenException(
                message: 'Invalid or expired refresh token.',
                previous: $e
            );
        }
    }

    public function proxyRefreshTokenGrant(string $refreshToken): TokenDTO
    {
        $response = $this->makeInternalRequest(
            parameters: [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]
        );

        return TokenDTO::fromArray($response);
    }

    public function logout(User $user): void
    {
        $accessToken = $user->token();

        if (! $accessToken) {
            return;
        }

        $accessToken->revoke();
        $accessToken->refreshToken?->revoke();
    }

    private function makeInternalRequest(array $parameters): array
    {
        $parameters['client_id'] = config('services.passport.password_client_id');
        $parameters['client_secret'] = config('services.passport.password_client_secret');
        $parameters['scope'] = '*'; // Token scopes are for clients only, not for users.

        $request = Request::create(
            uri: route(
                name: 'api.v1.auth.oauth.token',
                absolute: false
            ),
            method: 'POST',
            parameters: $parameters
        );

        $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
        $request->headers->set('Accept', 'application/json');

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
                message: $result['error_description'] ?? 'Invalid credentials or expired token.'
            );
        }

        return $result;
    }
}
