<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\ConfirmTwoFactor;
use App\Http\Requests\Api\V1\Auth\ConfirmTwoFactorRequest;
use App\Http\Resources\Api\V1\Auth\RecoveryCodesResource;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class ConfirmTwoFactorController
{
    use HasApiResponse;

    public function __construct(
        private readonly ConfirmTwoFactor $action
    ) {}

    public function __invoke(ConfirmTwoFactorRequest $request): JsonResponse
    {
        $recoveryCodes = $this->action->handle(
            user: $request->user(),
            code: $request->validated('code')
        );

        return $this->success(
            data: new RecoveryCodesResource($recoveryCodes),
            message: 'Two-factor authentication verified and enabled.'
        );
    }
}
