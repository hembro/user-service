<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\SocialLoginCommand;
use App\DTOs\Auth\AuthenticationOutcome;
use App\DTOs\Auth\SocialProfile;
use App\Enums\UserStatus;
use App\Events\Auth\UserLoggedIn;
use App\Events\Users\UserRegistered;
use App\Exceptions\Auth\InvalidCredentialsException;
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

    public function handle(SocialLoginCommand $command): AuthenticationOutcome
    {
        try {
            $socialiteUser = Socialite::driver($command->provider->value)
                ->stateless()
                ->user();
        } catch (Throwable $e) {

            $this->logger->error(
                'Social Login Failed',
                [
                    'provider' => $command->provider->value,
                    'exception' => $e,
                ]
            );

            throw new InvalidCredentialsException("Failed to validate token with {$command->provider->value}.");
        }

        $user = $this->resolver->resolve(
            profile: SocialProfile::fromSocialite($command->provider, $socialiteUser),
            system: $command->system
        );

        if ($user->status !== UserStatus::ACTIVE) {
            throw new InvalidCredentialsException('Account is inactive.');
        }

        $this->deviceService->trustDevice(
            user: $user,
            deviceId: $command->deviceId
        );

        $user->loadMissing('roles');

        if ($user->wasRecentlyCreated) {
            UserRegistered::dispatch($user, $command->system);
        }

        UserLoggedIn::dispatch($user, $command->deviceId, $command->system);

        return AuthenticationOutcome::authenticated(
            token: $this->tokenIssuer->issueFullToken($user, $command->system),
            deviceId: $command->deviceId
        );
    }
}
