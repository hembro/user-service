<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Admin\Users\DeleteUser;
use App\Commands\Admin\Users\DeleteUserCommand;
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
            DeleteUserCommand::fromRequest($request, $user)
        );

        return $this->noContent();
    }
}
