<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Api\V1\Auth\LogoutUser;
use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LogoutController
{
    use HasApiResponse;

    public function __construct(
        private readonly LogoutUser $action,
        private readonly AuthCookieService $cookie
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->action->handle(
            user: $request->user(),
            metadata: RequestMetadata::fromRequest($request)
        );

        return $this->noContent()
            ->withCookie(
                $this->cookie->forgetRefreshToken()
            );
    }
}
