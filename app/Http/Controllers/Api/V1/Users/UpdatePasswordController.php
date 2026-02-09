<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Actions\Api\V1\Users\UpdatePassword;
use App\DTOs\Api\V1\Users\UpdatePasswordDTO;
use App\Http\Requests\Api\V1\Users\UpdatePasswordRequest;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class UpdatePasswordController
{
    use HasApiResponse;

    public function __construct(
        private readonly UpdatePassword $action
    ) {}

    public function __invoke(UpdatePasswordRequest $request): JsonResponse
    {
        $this->action->handle(
            dto: UpdatePasswordDTO::fromRequest($request),
            user: $request->user()
        );

        return $this->success(
            message: 'Password updated successfully. Other active tokens have been terminated.'
        );
    }
}
