<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Auth;

use App\Actions\Api\V1\Admin\Auth\ImpersonateUser;
use App\Events\Admin\UserImpersonated;
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
        $token = $this->action->handle(
            target: $user,
            system: $request->attributes->get('system')
        );

        UserImpersonated::dispatch($user, $request->user());

        return $this->success(
            data: new TokenResource($token),
            message: "Impersonating {$user->email}"
        );
    }
}
