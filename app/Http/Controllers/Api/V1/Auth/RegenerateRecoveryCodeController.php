<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Api\V1\Auth\RegenerateRecoveryCodes;
use App\Http\Requests\Api\V1\Auth\RegenerateRecoveryCodesRequest;
use App\Http\Resources\Api\V1\Auth\RecoveryCodesResource;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class RegenerateRecoveryCodeController
{
    use HasApiResponse;

    public function __construct(
        private readonly RegenerateRecoveryCodes $action
    ) {}

    public function __invoke(RegenerateRecoveryCodesRequest $request): JsonResponse
    {
        $recoveryCodes = $this->action->handle(
            user: $request->user()
        );

        return $this->success(
            data: new RecoveryCodesResource($recoveryCodes),
            message: 'Recovery codes regenerated successfully.'
        );
    }
}
