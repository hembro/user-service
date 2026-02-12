<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Api\V1\Admin\Users\ImpersonateUser;
use App\Http\Resources\Api\V1\Auth\TokenResource;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Http\Request;

final class ImpersonateController
{
    use HasApiResponse;

    public function __construct(
        private readonly ImpersonateUser $action
    ) {}

    public function __invoke(Request $request, User $user)
    {
        return $this->success(
            data: new TokenResource(
                resource: $this->action->handle(
                    admin: $request->user(),
                    target: $user,
                    system: $request->attributes->get('system')
                )
            ),
            message: "Impersonating {$user->email}"
        );
    }
}
