<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Actions\Users\UpdateAvatar;
use App\DTOs\Api\V1\Users\UpdateAvatarData;
use App\Http\Requests\Api\V1\Users\UpdateAvatarRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class UpdateAvatarController
{
    use HasApiResponse;

    public function __construct(
        private readonly UpdateAvatar $action
    ) {}

    public function __invoke(UpdateAvatarRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $this->action->handle(
            dto: UpdateAvatarData::fromRequest($request, $user)
        );

        return $this->success(
            data: new UserResource($user->refresh()->load('profile')),
            message: 'Avatar updated successfully.'
        );
    }
}
