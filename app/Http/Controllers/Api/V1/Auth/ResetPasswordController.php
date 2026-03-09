<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\ResetUserPassword;
use App\Commands\Auth\ResetPasswordCommand;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use Illuminate\Http\JsonResponse;

final class ResetPasswordController
{
    public function __construct(
        private readonly ResetUserPassword $action
    ) {}

    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $this->action->handle(
            ResetPasswordCommand::fromRequest($request)
        );

        return JsonResponse::success(
            message: 'Your password has been reset successfully. Please login with your new password.'
        );
    }
}
