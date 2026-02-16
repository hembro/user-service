<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\AuthenticationOutcomeDTO;
use App\DTOs\Api\V1\Auth\SocialLoginDTO;
use App\DTOs\Api\V1\Auth\SocialUserDTO;
use App\Enums\UserStatus;
use App\Events\Auth\UserLoggedIn;
use App\Events\Users\UserRegistered;
use App\Exceptions\InvalidCredentialsException;
use App\Services\Auth\DeviceTrustService;
use App\Services\Auth\SocialUserResolver;
use App\Services\Auth\TokenIssuer;
use Laravel\Socialite\Facades\Socialite;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class ProcessSocialLogin
{
    public function __construct(
        private SocialUserResolver $resolver,
        private DeviceTrustService $deviceService,
        private TokenIssuer $tokenIssuer,
        private LoggerInterface $logger
    ) {}

    public function handle(SocialLoginDTO $dto): AuthenticationOutcomeDTO
    {
        try {
            $socialiteUser = Socialite::driver($dto->provider->value)
                ->stateless()
                ->user();
        } catch (Throwable $e) {
            $this->logger->error(
                message: 'Social Login Failed',
                context: [
                    'provider' => $dto->provider->value,
                    'exception' => $e,
                ]
            );

            throw new InvalidCredentialsException("Failed to validate token with {$dto->provider->value}.");
        }

        $user = $this->resolver->resolve(
            dto: SocialUserDTO::fromSocialite(
                provider: $dto->provider,
                socialUser: $socialiteUser
            ),
            system: $dto->system
        );

        if ($user->status !== UserStatus::ACTIVE) {
            throw new InvalidCredentialsException('Account is inactive.');
        }

        $this->deviceService->trustDevice(
            user: $user,
            deviceId: $dto->deviceId,
            metadata: $dto->metadata
        );

        if ($user->wasRecentlyCreated) {
            UserRegistered::dispatch($user);
        }

        UserLoggedIn::dispatch($user, $dto->deviceId, $dto->metadata);

        return AuthenticationOutcomeDTO::authenticated(
            token: $this->tokenIssuer->issueFullToken($user, $dto->system),
            deviceId: $dto->deviceId
        );
    }
}
