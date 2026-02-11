<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Enums\SocialProviders;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

final class SocialAuthController
{
    use HasApiResponse;

    public function redirect(SocialProviders $provider): JsonResponse
    {
        $url = Socialite::driver($provider->value)
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return $this->success(
            data: [
                'provider' => $provider->value,
                'redirect_url' => $url,
            ]
        );
    }

    public function callback(Request $request, SocialProviders $provider): JsonResponse
    {
        return $this->success();
    }
}
