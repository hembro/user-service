<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Actions\Users\ConfirmEmailChange;
use App\Actions\Users\InitiateEmailChange;
use App\Commands\Users\InitiateEmailChangeCommand;
use App\Commands\Users\VerifyEmailChangeCommand;
use App\Http\Requests\Api\V1\Users\RequestEmailChangeRequest;
use App\Http\Requests\Api\V1\Users\VerifyEmailChangeRequest;
use Illuminate\Http\JsonResponse;

final class EmailChangeController
{
    public function request(RequestEmailChangeRequest $request, InitiateEmailChange $action): JsonResponse
    {
        $action->handle(
            InitiateEmailChangeCommand::fromRequest($request)
        );

        return JsonResponse::success(
            message: 'A verification link has been sent to your email address.'
        );
    }

    public function verify(VerifyEmailChangeRequest $request, ConfirmEmailChange $action): JsonResponse
    {
        $action->handle(
            VerifyEmailChangeCommand::fromRequest($request),
        );

        return JsonResponse::success(
            message: 'Your email address has been updated successfully.'
        );
    }
}
