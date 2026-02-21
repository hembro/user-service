<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\ResetUserPassword;
use App\DTOs\Api\V1\Auth\ResetPasswordData;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class ResetPasswordController
{
    use HasApiResponse;

    public function __construct(
        private readonly ResetUserPassword $action
    ) {}

    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $this->action->handle(
            dto: ResetPasswordData::fromRequest($request)
        );

        return $this->success(
            message: 'Your password has been reset successfully. Please login with your new password.'
        );
    }
}
