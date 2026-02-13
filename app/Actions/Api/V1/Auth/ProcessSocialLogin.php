<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\AuthenticationOutcomeDTO;
use App\DTOs\Api\V1\Auth\SocialLoginDTO;
use App\DTOs\Api\V1\Auth\SocialUserDTO;
use App\Enums\UserStatus;
use App\Events\Auth\DeviceUsed;
use App\Events\Auth\UserLoggedIn;
use App\Events\Users\UserRegistered;
use App\Exceptions\InvalidCredentialsException;
use App\Services\Auth\DeviceTrustService;
use App\Services\Auth\SocialUserResolver;
use App\Services\Auth\TokenIssuer;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

final readonly class ProcessSocialLogin
{
    public function __construct(
        private SocialUserResolver $resolver,
        private DeviceTrustService $deviceService,
        private TokenIssuer $tokenIssuer,
    ) {}

    public function handle(SocialLoginDTO $dto): AuthenticationOutcomeDTO
    {
        try {
            $socialiteUser = Socialite::driver(
                driver: $dto->provider->value
            )->stateless()->user();
        } catch (Throwable $e) {
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

        $deviceUuid = $dto->metadata->deviceId ?? Str::uuid()->toString();

        $this->deviceService->authorizeDevice(
            user: $user,
            deviceUuid: $deviceUuid,
            metadata: $dto->metadata
        );

        if ($user->wasRecentlyCreated) {
            UserRegistered::dispatch($user);
        }

        DeviceUsed::dispatch($user, $dto->metadata);
        UserLoggedIn::dispatch($user, $dto->metadata);

        return AuthenticationOutcomeDTO::authenticated(
            token: $this->tokenIssuer->issueFullToken($user, $dto->system),
            deviceId: $deviceUuid
        );
    }
}
