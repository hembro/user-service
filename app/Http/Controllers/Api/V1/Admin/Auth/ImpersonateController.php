<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Auth;

use App\Actions\Admin\Auth\ImpersonateUser;
use App\Commands\Admin\Auth\ImpersonateUserCommand;
use App\Http\Requests\Api\V1\Admin\Users\ImpersonateUserRequest;
use App\Http\Resources\Api\V1\Auth\TokenResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

final class ImpersonateController
{
    public function __construct(
        private readonly ImpersonateUser $action
    ) {}

    public function __invoke(ImpersonateUserRequest $request, User $user): JsonResponse
    {
        $token = $this->action->handle(
            ImpersonateUserCommand::fromRequest($request, $user)
        );

        return JsonResponse::success(
            data: new TokenResource($token),
            message: "Impersonating {$user->email}"
        );
    }
}
