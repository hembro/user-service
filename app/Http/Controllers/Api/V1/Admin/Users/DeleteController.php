<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Api\V1\Admin\Users\DeleteUser;
use App\DTOs\Api\V1\Admin\Users\DeleteUserDTO;
use App\Http\Requests\Api\V1\Admin\Users\DeleteRequest as AdminDeleteRequest;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class DeleteController
{
    use HasApiResponse;

    public function __construct(
        private readonly DeleteUser $action
    ) {}

    public function __invoke(AdminDeleteRequest $request, User $user): JsonResponse
    {
        $this->action->handle(
            dto: DeleteUserDTO::fromRequest($request),
            user: $user,
            admin: $request->user()
        );

        return $this->noContent();
    }
}
