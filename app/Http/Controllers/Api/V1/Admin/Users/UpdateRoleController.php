<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Admin\Users\UpdateUserRole;
use App\Commands\Admin\Users\UpdateRoleCommand;
use App\Http\Requests\Api\V1\Admin\Users\UpdateRoleRequest as AdminUpdateRoleRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class UpdateRoleController
{
    public function __construct(
        private UpdateUserRole $action
    ) {}

    public function __invoke(AdminUpdateRoleRequest $request, User $user): JsonResponse
    {
        $this->action->handle(
            UpdateRoleCommand::fromRequest($request, $user),
        );

        return JsonResponse::success(
            data: new UserResource($user->refresh()->load(['profile', 'roles.permissions', 'permissions'])),
            message: 'User role updated successfully'
        );
    }
}
