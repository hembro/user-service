<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Api\V1\Admin\Users\ResetPassword;
use App\DTOs\Api\V1\Admin\Users\ResetPasswordData;
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
            dto: ResetPasswordData::fromRequest($request, $user),
        );

        return $this->success(
            message: 'User password reset successfully and all tokens are terminated.',
        );
    }
}
