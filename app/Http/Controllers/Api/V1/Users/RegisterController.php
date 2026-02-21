<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Actions\Users\RegisterUser;
use App\DTOs\Api\V1\Users\RegisterUserData;
use App\Http\Requests\Api\V1\Users\RegisterRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Services\AuthCookieService;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class RegisterController
{
    use HasApiResponse;

    public function __construct(
        private readonly RegisterUser $action,
        private readonly AuthCookieService $cookie
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $deviceId = (string) Str::orderedUuid();

        $user = $this->action->handle(
            dto: RegisterUserData::fromRequest($request, $deviceId)
        );

        return $this->success(
            data: new UserResource($user),
            message: 'User created successfully',
            code: Response::HTTP_CREATED
        )
            ->withCookie(
                $this->cookie->makeDeviceIdCookie($deviceId)
            );
    }
}
