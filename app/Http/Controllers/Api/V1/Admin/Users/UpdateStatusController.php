<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Api\V1\Admin\Users\UpdateUserStatus;
use App\DTOs\Api\V1\Admin\Users\UpdateUserStatusDTO;
use App\Http\Requests\Api\V1\Admin\Users\UpdateStatusRequest as AdminUpdateStatusRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Models\User;
use App\Traits\HasApiResponse;

final class UpdateStatusController
{
    use HasApiResponse;

    public function __construct(
        private readonly UpdateUserStatus $action
    ) {}

    public function __invoke(AdminUpdateStatusRequest $request, User $user)
    {
        $this->action->handle(
            dto: UpdateUserStatusDTO::fromRequest($request),
            user: $user,
            admin: $request->user()
        );

        return $this->success(
            data: new UserResource($user->refresh()->load('profile')),
            message: 'User status updated successfully'
        );
    }
}
