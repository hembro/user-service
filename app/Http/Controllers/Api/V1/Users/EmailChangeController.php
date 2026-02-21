<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Actions\Users\ConfirmEmailChange;
use App\Actions\Users\InitiateEmailChange;
use App\DTOs\Api\V1\Users\InitiateEmailChangeData;
use App\DTOs\Api\V1\Users\VerifyEmailChangeData;
use App\Http\Requests\Api\V1\Users\RequestEmailChangeRequest;
use App\Http\Requests\Api\V1\Users\VerifyEmailChangeRequest;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class EmailChangeController
{
    use HasApiResponse;

    public function request(RequestEmailChangeRequest $request, InitiateEmailChange $action): JsonResponse
    {
        $action->handle(
            dto: InitiateEmailChangeData::fromRequest($request)
        );

        return $this->success(
            message: 'A verification link has been sent to your email address.'
        );
    }

    public function verify(VerifyEmailChangeRequest $request, ConfirmEmailChange $action): JsonResponse
    {
        $action->handle(
            dto: VerifyEmailChangeData::fromRequest($request),
        );

        return $this->success(
            message: 'Your email address has been updated successfully.'
        );
    }
}
