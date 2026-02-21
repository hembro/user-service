<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\VerifyAuthenticationChallenge;
use App\DTOs\Api\V1\Auth\VerifyChallengeDTO;
use App\DTOs\Shared\RequestMetadata;
use App\Http\Requests\Api\V1\Auth\VerifyChallengeRequest;
use App\Http\Resources\Api\V1\Auth\AuthResource;
use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class VerifyAuthenticationChallengeController
{
    use HasApiResponse;

    public function __construct(
        private readonly VerifyAuthenticationChallenge $action,
        private readonly AuthCookieService $cookie
    ) {}

    public function __invoke(VerifyChallengeRequest $request): JsonResponse
    {
        $outcome = $this->action->handle(
            verifyChallengedto: VerifyChallengeDTO::fromRequest($request),
            metadata: RequestMetadata::fromRequest($request)
        );

        return $this->success(
            data: new AuthResource($outcome),
            message: 'Authentication successful.'
        )->withCookie(
            $this->cookie->makeRefreshTokenCookie($outcome->token->refreshToken)
        )->withCookie(
            $this->cookie->makeDeviceIdCookie($outcome->deviceId)
        );
    }
}
