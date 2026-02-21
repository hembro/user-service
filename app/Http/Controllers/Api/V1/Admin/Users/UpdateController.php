<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Admin\Users\UpdateUser;
use App\DTOs\Api\V1\Admin\Users\UpdateUserData;
use App\Http\Requests\Api\V1\Admin\Users\UpdateRequest as AdminUpdateRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class UpdateController
{
    use HasApiResponse;

    public function __construct(
        private readonly UpdateUser $action
    ) {}

    public function __invoke(AdminUpdateRequest $request, User $user): JsonResponse
    {
        $this->action->handle(
            dto: UpdateUserData::fromRequest($request, $user),
        );

        return $this->success(
            data: new UserResource($user->refresh()->load(['profile', 'roles.permissions', 'permissions'])),
            message: 'User updated successfully'
        );
    }
}
