<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Api\V1\Auth\GetSocialRedirectUrl;
use App\DTOs\Api\V1\Auth\SocialRedirectDTO;
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
            dto: new SocialRedirectDTO(
                provider: $provider,
                system: $request->attributes->get('system')
            ),
        );

        return $this->success(data: ['url' => $url]);
    }
}
