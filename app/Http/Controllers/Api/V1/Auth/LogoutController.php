<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\DTOs\Api\V1\Shared\RequestMetadata;
use App\Services\AuthCookieService;
use App\Services\AuthService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class LogoutController
{
    use HasApiResponse;

    public function __construct(
        private readonly AuthService $service,
        private readonly AuthCookieService $cookieFactory
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->service->logout(
            user: $request->user(),
            metadata: RequestMetadata::fromRequest($request)
        );

        return $this->noContent()
            ->withCookie(
                cookie: $this->cookieFactory->forget()
            );
    }
}
