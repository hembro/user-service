<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Admin\Users\RestoreUser;
use App\Commands\Admin\Users\RestoreUserCommand;
use App\Http\Requests\Api\V1\Admin\Users\RestoreRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class RestoreController
{
    use HasApiResponse;

    public function __construct(
        private readonly RestoreUser $action
    ) {}

    public function __invoke(RestoreRequest $request, User $user): JsonResponse
    {
        $this->action->handle(
            RestoreUserCommand::fromRequest($request, $user),
        );

        return $this->success(
            data: new UserResource($user->refresh()->load(['profile', 'roles'])),
            message: 'User restored successfully.'
        );
    }
}
