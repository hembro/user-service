<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\SendResetPasswordLink;
use App\Commands\Auth\ForgotPasswordCommand;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use Illuminate\Http\JsonResponse;

final class ForgotPasswordController
{
    public function __construct(
        private readonly SendResetPasswordLink $action
    ) {}

    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        $this->action->handle(
            ForgotPasswordCommand::fromRequest($request)
        );

        return JsonResponse::success(
            message: 'We sent your password reset link!'
        );
    }
}
