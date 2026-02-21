<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\EnableTwoFactor;
use App\Http\Resources\Api\V1\Auth\TwoFactorSetupResource;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EnableTwoFactorController
{
    use HasApiResponse;

    public function __construct(
        private readonly EnableTwoFactor $action
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string', 'min:8', 'max:255', 'current_password:api'],
        ]);

        $dto = $this->action->handle($request->user());

        return $this->success(
            data: new TwoFactorSetupResource($dto),
            message: 'Scan the QR code to finish setup.'
        );
    }
}
