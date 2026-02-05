<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Api\V1\Admin\Users\AdminUpdateUser;
use App\DTOs\Api\V1\Admin\Users\UpdateUserDTO;
use App\Http\Requests\Api\V1\Admin\Users\UpdateRequest as AdminUpdateRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class UpdateController
{
    use HasApiResponse;

    public function __construct(
        private readonly AdminUpdateUser $action
    ) {}

    public function __invoke(AdminUpdateRequest $request, User $user): JsonResponse
    {
        $updatedUser = $this->action->handle(
            user: $user,
            dto: UpdateUserDTO::fromArray($request->validated()),
            admin: $request->user()
        );

        return $this->success(
            data: new UserResource($updatedUser),
            message: 'User updated successfully',
            code: Response::HTTP_OK
        );
    }
}
