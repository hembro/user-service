<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Actions\Users\RegisterUser;
use App\Commands\Users\RegisterUserCommand;
use App\Contracts\Auth\DeviceTrustVerifier;
use App\Http\Requests\Api\V1\Users\RegisterRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Services\AuthCookieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class RegisterController
{
    public function __construct(
        private readonly RegisterUser $action,
        private readonly DeviceTrustVerifier $deviceService,
        private readonly AuthCookieService $cookie
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $deviceId = $this->deviceService->resolveDeviceId($request) ?? (string) Str::ulid();

        $user = $this->action->handle(
            RegisterUserCommand::fromRequest($request, $deviceId)
        );

        return JsonResponse::success(
            data: new UserResource($user),
            message: 'User created successfully',
            code: Response::HTTP_CREATED
        )
            ->withCookie(
                $this->cookie->makeDeviceIdCookie($deviceId)
            );
    }
}
