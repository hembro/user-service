<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Api\V1\Admin\Users\UpdateUserRole;
use App\DTOs\Api\V1\Admin\Users\UpdateRoleDTO;
use App\Http\Requests\Api\V1\Admin\Users\UpdateRoleRequest as AdminUpdateRoleRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Models\User;
use App\Traits\HasApiResponse;

final class UpdateRoleController
{
    use HasApiResponse;

    public function __construct(
        private UpdateUserRole $action
    ) {}

    public function __invoke(AdminUpdateRoleRequest $request, User $user)
    {
        $this->action->handle(
            dto: UpdateRoleDTO::fromRequest($request),
            user: $user,
            admin: $request->user()
        );

        return $this->success(
            data: new UserResource($user->refresh()->load(['profile', 'roles.permissions', 'permissions'])),
            message: 'User role updated successfully'
        );
    }
}
