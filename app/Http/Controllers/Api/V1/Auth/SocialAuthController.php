<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Api\V1\Auth\HandleSocialLogin;
use App\Enums\SocialProviders;
use App\Http\Requests\Api\V1\Auth\SocialLoginRequest;
use App\Http\Resources\Api\V1\Auth\AuthUserResource;
use App\Http\Resources\Api\V1\Auth\TokenResource;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;

final class SocialAuthController
{
    use HasApiResponse;

    public function redirect(SocialProviders $provider): JsonResponse
    {
        return $this->success(
            data: [
                'provider' => $provider->value,
                'redirect_url' => Socialite::driver($provider->value)->stateless()->redirect()->getTargetUrl(),
            ]
        );
    }

    public function callback(
        SocialProviders $provider,
        SocialLoginRequest $request,
        HandleSocialLogin $action
    ): JsonResponse {

        $data = $action->handle(
            request: $request,
            provider: $provider
        );

        return $this->success(
            message: 'Authentication successful.',
            data: [
                'user' => new AuthUserResource($data['user']),
                'token' => new TokenResource($data['token']),
            ]
        );
    }
}
