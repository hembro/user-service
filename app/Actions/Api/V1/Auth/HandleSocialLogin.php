<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\SocialUserDTO;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\SocialProviders;
use App\Enums\Systems;
use App\Exceptions\InvalidCredentialsException;
use App\Http\Requests\Api\V1\Auth\SocialLoginRequest;
use App\Services\AuthService;
use GuzzleHttp\Exception\ClientException;
use Laravel\Socialite\Facades\Socialite;

final readonly class HandleSocialLogin
{
    public function __construct(
        private CreateSocialUser $createSocialUser,
        private AuthService $authService
    ) {}

    public function handle(SocialLoginRequest $request, SocialProviders $provider): array
    {
        try {
            $socialUser = Socialite::driver($provider->value)->stateless()->user();
        } catch (ClientException $e) {
            throw new InvalidCredentialsException(
                message: 'Invalid or expired social authentication code.'
            );
        }

        /** @var Systems $system */
        $system = $request->attributes->get('system');

        $user = $this->createSocialUser->handle(
            dto: SocialUserDTO::fromSocialite($provider, $socialUser),
            system: $system
        );

        $tokenDto = $this->authService->socialLogin(
            user: $user,
            system: $system,
            metadata: RequestMetadata::fromRequest($request)
        );

        return [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'is_new_user' => $user->wasRecentlyCreated,
            ],
            'tokens' => $tokenDto,
        ];
    }
}
