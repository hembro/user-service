<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Api\V1\Tokens\TokenDTO;
use App\Enums\Users\UserStatus;
use App\Events\UserLoggedIn;
use App\Exceptions\AccountStatusException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidRefreshTokenException;
use App\Exceptions\UpstreamServiceException;
use App\Models\User;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\TokenRepository;
use RuntimeException;
use SensitiveParameter;

final class AuthService
{
    public function __construct(
        private readonly TokenRepository $tokenRepository,
        private readonly RefreshTokenRepository $refreshTokenRepository
    ) {}

    public function login(
        string $email,
        #[SensitiveParameter]
        string $password,
        string $ip,
        string $userAgent
    ): TokenDTO {
        $user = User::where(
            column: 'email',
            value: $email,
            operator: '=',
            boolean: 'and'
        )->firstOrFail();

        if ($user->status !== UserStatus::ACTIVE) {
            throw new AccountStatusException(
                message: "Access denied. Account status: {$user->status->value}"
            );
        }

        if (! $user || ! Hash::check($password, $user->password)) {
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
        // Laravel Octane Ready!
        $response = Http::asForm()->post(
            url: route('api.v1.auth.oauth.token'),
            data: [
                'grant_type' => 'password',
                'client_id' => config('services.passport.password_client_id'),
                'client_secret' => config('services.passport.password_client_secret'),
                'username' => $email,
                'password' => $password,
                'scope' => '*',
            ]
        );

        return TokenDTO::fromArray($this->handleResponse($response));
    }

    public function refresh(string $refreshToken): TokenDTO
    {
        try {
            return $this->proxyRefreshTokenGrant(
                refreshToken: $refreshToken
            );
        } catch (AuthenticationException $e) {
            throw new InvalidRefreshTokenException();
        }
    }

    public function proxyRefreshTokenGrant(string $refreshToken): TokenDTO
    {
        // Laravel Octane Ready!
        $response = Http::asForm()->post(
            url: route('api.v1.auth.oauth.token'),
            data: [
                'grant_type' => 'refresh_token',
                'client_id' => config('services.passport.password_client_id'),
                'client_secret' => config('services.passport.password_client_secret'),
                'refresh_token' => $refreshToken,
                'scope' => '*',
            ]
        );

        return TokenDTO::fromArray($this->handleResponse($response));
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

    private function handleResponse(PromiseInterface|Response $response): array
    {
        if (! $response instanceof Response) {
            throw new RuntimeException(
                'Critical Error: Auth service returned an unexpected response type.'
            );
        }

        if ($response->failed()) {
            if ($response->serverError()) {
                throw new UpstreamServiceException(
                    'The authentication server is currently unavailable. Please try again later.'
                );
            }

            throw new InvalidCredentialsException(
                message: $response->json('error') ?? 'Invalid or expired token.'
            );
        }

        return $response->json();
    }
}
