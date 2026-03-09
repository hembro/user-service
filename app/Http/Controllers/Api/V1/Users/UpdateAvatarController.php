<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Actions\Users\UpdateAvatar;
use App\Commands\Users\UpdateAvatarCommand;
use App\Http\Requests\Api\V1\Users\UpdateAvatarRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use Illuminate\Http\JsonResponse;

final class UpdateAvatarController
{
    public function __construct(
        private readonly UpdateAvatar $action
    ) {}

    public function __invoke(UpdateAvatarRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->action->handle(
            UpdateAvatarCommand::fromRequest($request, $user)
        );

        return JsonResponse::success(
            data: new UserResource($user->refresh()->load('profile')),
            message: 'Avatar updated successfully.'
        );
    }
}
