<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Api\V1\Admin\Users\UpdateUser;
use App\DTOs\Api\V1\Admin\Users\UpdateUserDTO;
use App\Http\Requests\Api\V1\Admin\Users\UpdateRequest as AdminUpdateRequest;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class UpdateController
{
    use HasApiResponse;

    public function __construct(
        private readonly UpdateUser $action
    ) {}

    public function __invoke(AdminUpdateRequest $request, User $user): JsonResponse
    {
        $this->action->handle(
            dto: UpdateUserDTO::fromRequest($request),
            user: $user,
            admin: $request->user()
        );

        return $this->noContent();
    }
}
