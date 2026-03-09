<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Actions\Users\UpdateProfile;
use App\Commands\Users\UpdateProfileCommand;
use App\Http\Requests\Api\V1\Users\UpdateProfileRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use Illuminate\Http\JsonResponse;

final class UpdateProfileController
{
    public function __construct(
        private readonly UpdateProfile $action
    ) {}

    public function __invoke(UpdateProfileRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->action->handle(
            UpdateProfileCommand::fromRequest($request, $user)
        );

        return JsonResponse::success(
            data: new UserResource($user->refresh()->load(['profile', 'roles.permissions', 'permissions'])),
            message: 'User profile updated successfully'
        );
    }
}
