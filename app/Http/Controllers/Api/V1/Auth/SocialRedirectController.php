<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\GetSocialRedirectUrl;
use App\Commands\Auth\SocialRedirectCommand;
use App\Enums\SocialProviders;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SocialRedirectController
{
    use HasApiResponse;

    public function __construct(
        private readonly GetSocialRedirectUrl $action
    ) {}

    public function __invoke(Request $request, SocialProviders $provider): JsonResponse
    {
        $url = $this->action->handle(
            new SocialRedirectCommand($provider, $request->attributes->get('system')),
        );

        return $this->success(
            data: [
                'provider' => $provider->value,
                'redirect_url' => $url,
            ]
        );
    }
}
