<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Resources\Api\V1\Users\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ShowProfileController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user()->load(['profile', 'roles.permissions', 'permissions']);

        return JsonResponse::success(
            data: new UserResource($user)
        );
    }
}
