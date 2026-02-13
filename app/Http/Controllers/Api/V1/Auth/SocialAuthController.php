<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Api\V1\Auth\ProcessSocialLogin;
use App\DTOs\Api\V1\Auth\SocialLoginDTO;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Enums\SocialProviders;
use App\Http\Requests\Api\V1\Auth\SocialLoginRequest;
use App\Http\Resources\Api\V1\Auth\AuthResource;
use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class SocialAuthController
{
    use HasApiResponse;

    public function __construct(
        private readonly ProcessSocialLogin $action,
        private readonly AuthCookieService $cookieService
    ) {}

    public function __invoke(SocialLoginRequest $request, SocialProviders $provider): JsonResponse
    {
        $authentocationOutcome = $this->action->handle(
            dto: new SocialLoginDTO(
                provider: $provider,
                code: $request->validated('code'),
                system: $request->attributes->get('system'),
                metadata: RequestMetadata::fromRequest($request)
            )
        );

        $response = $this->success(
            data: new AuthResource($authentocationOutcome),
            message: 'Social authentication successful.'
        );

        if ($authentocationOutcome->token) {
            $response->withCookie(
                $this->cookieService->makeRefreshTokenCookie($authentocationOutcome->token->refreshToken)
            );
        }

        if ($authentocationOutcome->deviceId) {
            $response->withCookie(
                $this->cookieService->makeDeviceIdCookie($authentocationOutcome->deviceId)
            );
        }

        return $response;
    }
}
