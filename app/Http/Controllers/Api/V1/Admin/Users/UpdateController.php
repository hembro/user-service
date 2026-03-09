<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Admin\Users\UpdateUser;
use App\Commands\Admin\Users\UpdateUserCommand;
use App\Http\Requests\Api\V1\Admin\Users\UpdateRequest as AdminUpdateRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class UpdateController
{
    public function __construct(
        private readonly UpdateUser $action
    ) {}

    public function __invoke(AdminUpdateRequest $request, User $user): JsonResponse
    {
        $this->action->handle(
            UpdateUserCommand::fromRequest($request, $user),
        );

        return JsonResponse::success(
            data: new UserResource($user->refresh()->load(['profile', 'roles.permissions', 'permissions'])),
            message: 'User updated successfully'
        );
    }
}
