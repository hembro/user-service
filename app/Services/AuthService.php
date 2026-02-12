<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Api\V1\Auth\LoginCredentials;
use App\DTOs\Api\V1\Auth\RefreshTokenDTO;
use App\DTOs\Api\V1\Auth\TokenDTO;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\SocialProviders;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserLoggedOut;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\InvalidRefreshTokenException;
use App\Models\User;
use App\Services\Auth\OAuthTokenBroker;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Log\LoggerInterface;
use RuntimeException;

final readonly class AuthService
{
    public function __construct(
        private OAuthTokenBroker $broker,
        private LoggerInterface $logger
    ) {}

    public function login(LoginCredentials $credentials, RequestMetadata $metadata): TokenDTO
    {
        $user = User::query()
            ->with('roles')
            ->where('email', $credentials->email)
            ->first();

        $this->ensureActiveUser($user);

        try {
            $tokenDto = $this->broker->issueToken([
                'grant_type' => 'password',
                'username' => $credentials->email,
                'password' => $credentials->password,
                'scope' => $this->resolveScopes($user),
            ], $credentials->system);
        } catch (OAuthServerException $e) {
            $this->logger->error('OAuth Password Login Failed', ['exception' => $e]);
            throw new InvalidCredentialsException('Invalid credentials.');
        }

        UserLoggedIn::dispatch($user, $metadata);

        return $tokenDto;
    }

    public function socialLogin(User $user, Systems $system, SocialProviders $provider, RequestMetadata $metadata): TokenDTO
    {
        $this->ensureActiveUser($user);
        $user->loadMissing('roles');

        try {
            $tokenDto = $this->broker->issueToken([
                'grant_type' => 'social',
                'user_id' => $user->id,
                'provider' => $provider->value,
                'internal_signature' => config('app.key'),
                'scope' => $this->resolveScopes($user),
            ], $system);
        } catch (OAuthServerException $e) {
            $this->logger->error('OAuth Social Grant Failed', ['exception' => $e]);
            throw new InvalidCredentialsException('Failed to authenticate social credentials.');
        }

        UserLoggedIn::dispatch($user, $metadata);

        return $tokenDto;
    }

    public function impersonate(User $admin, User $target, Systems $system): TokenDTO
    {
        $target->loadMissing('roles');

        try {
            return $this->broker->issueToken([
                'grant_type' => 'impersonate',
                'target_user_id' => $target->id,
                'admin_user_id' => $admin->id,
                'internal_signature' => config('app.key'),
                'scope' => $this->resolveScopes($target),
            ], $system);
        } catch (OAuthServerException $e) {
            $this->logger->error('OAuth Impersonation Failed', ['exception' => $e]);
            throw new RuntimeException('Failed to impersonate user.');
        }
    }

    public function refresh(RefreshTokenDTO $dto): TokenDTO
    {
        try {
            return $this->broker->issueToken([
                'grant_type' => 'refresh_token',
                'refresh_token' => $dto->refreshToken,
                'scope' => '',
            ], $dto->system);
        } catch (OAuthServerException $e) {
            $this->logger->error('OAuth Refresh Failed', ['exception' => $e]);
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

    private function ensureActiveUser(?User $user): void
    {
        if (! $user || $user->status !== UserStatus::ACTIVE) {
            throw new InvalidCredentialsException('Invalid credentials.');
        }
    }

    private function resolveScopes(User $user): string
    {
        return $user->roles->implode('name', ' ');
    }
}
