<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Api\V1\Auth\RefereshUserToken;
use App\DTOs\Api\V1\Auth\RefreshTokenDTO;
use App\Http\Requests\Api\V1\Auth\RefreshTokenRequest;
use App\Http\Resources\Api\V1\Auth\TokenResource;
use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;

final class RefreshTokenController
{
    use HasApiResponse;

    public function __construct(
        private readonly RefereshUserToken $action,
        private readonly AuthCookieService $cookie,
    ) {}

    public function __invoke(RefreshTokenRequest $request)
    {
        $token = $this->action->handle(
            dto: RefreshTokenDTO::fromRequest($request)
        );

        return $this->success(
            data: new TokenResource($token)
        )->withCookie(
            $this->cookie->makeRefreshTokenCookie($token->refreshToken)
        );
    }
}
