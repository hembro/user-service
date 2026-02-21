<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Auth;

use App\Actions\Api\V1\Admin\Auth\ImpersonateUser;
use App\DTOs\Api\V1\Admin\Users\ImpersonateUserData;
use App\Http\Requests\Api\V1\Admin\Users\ImpersonateUserRequest;
use App\Http\Resources\Api\V1\Auth\TokenResource;
use App\Models\User;
use App\Traits\HasApiResponse;

final class ImpersonateController
{
    use HasApiResponse;

    public function __construct(
        private readonly ImpersonateUser $action
    ) {}

    public function __invoke(ImpersonateUserRequest $request, User $user)
    {
        $token = $this->action->handle(
            dto: ImpersonateUserData::fromRequest($request, $user)
        );

        return $this->success(
            data: new TokenResource($token),
            message: "Impersonating {$user->email}"
        );
    }
}
