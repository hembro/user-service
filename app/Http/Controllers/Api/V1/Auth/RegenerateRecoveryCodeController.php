<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\RegenerateRecoveryCodes;
use App\Http\Requests\Api\V1\Auth\RegenerateRecoveryCodesRequest;
use App\Http\Resources\Api\V1\Auth\RecoveryCodesResource;
use Illuminate\Http\JsonResponse;

final class RegenerateRecoveryCodeController
{
    public function __construct(
        private readonly RegenerateRecoveryCodes $action
    ) {}

    public function __invoke(RegenerateRecoveryCodesRequest $request): JsonResponse
    {
        $recoveryCodes = $this->action->handle($request->user());

        return JsonResponse::success(
            data: new RecoveryCodesResource($recoveryCodes),
            message: 'Recovery codes regenerated successfully.'
        );
    }
}
