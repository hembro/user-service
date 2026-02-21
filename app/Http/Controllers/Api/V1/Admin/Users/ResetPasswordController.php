<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Admin\Users\ResetPassword;
use App\Commands\Admin\Users\ResetPasswordCommand;
use App\Http\Requests\Api\V1\Admin\Users\ResetPasswordRequest;
use App\Models\User;
use App\Traits\HasApiResponse;

final class ResetPasswordController
{
    use HasApiResponse;

    public function __construct(
        private readonly ResetPassword $action
    ) {}

    public function __invoke(ResetPasswordRequest $request, User $user)
    {
        $this->action->handle(
            ResetPasswordCommand::fromRequest($request, $user),
        );

        return $this->success(
            message: 'User password reset successfully and all tokens are terminated.',
        );
    }
}
