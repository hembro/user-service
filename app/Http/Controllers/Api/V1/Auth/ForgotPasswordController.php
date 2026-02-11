<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Api\V1\Auth\SendResetLink;
use App\DTOs\Api\V1\Auth\ForgotPasswordDTO;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class ForgotPasswordController
{
    use HasApiResponse;

    public function __construct(
        private readonly SendResetLink $action
    ) {}

    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        $this->action->handle(
            dto: ForgotPasswordDTO::fromRequest($request)
        );

        return $this->success(
            message: 'We sent your password reset link!'
        );
    }
}
