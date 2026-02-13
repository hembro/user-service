<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Api\V1\Auth\VerifyAuthenticationChallenge;
use App\DTOs\Api\V1\Auth\VerifyChallengeDTO;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Http\Requests\Api\V1\Auth\VerifyChallengeRequest;
use App\Http\Resources\Api\V1\Auth\AuthResource;
use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class VerifyChallengeController
{
    use HasApiResponse;

    public function __construct(
        private readonly VerifyAuthenticationChallenge $action,
        private readonly AuthCookieService $cookie
    ) {}

    public function __invoke(VerifyChallengeRequest $request): JsonResponse
    {
        $outcome = $this->action->handle(
            dto: VerifyChallengeDTO::fromRequest($request),
            metadata: RequestMetadata::fromRequest($request)
        );

        $response = $this->success(
            data: new AuthResource($outcome),
            message: 'Authentication successful.'
        );

        if ($outcome->deviceId) {
            $response->withCookie(
                cookie: $this->cookie->makeDeviceIdCookie($outcome->deviceId)
            );
        }

        return $response;
    }
}
