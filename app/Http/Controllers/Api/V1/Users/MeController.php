<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Http\Resources\Api\V1\Users\UserResource;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MeController
{
    use HasApiResponse;

    public function __invoke(Request $request): JsonResponse
    {
        return $this->success(
            data: new UserResource($request->user())
        );
    }
}
