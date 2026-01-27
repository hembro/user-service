<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Actions\Api\V1\Users\CreateUser;
use App\DTOs\Api\V1\Users\RegisterUserDTO;
use App\Http\Requests\Api\V1\Users\RegisterRequest;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class RegisterController
{
    use HasApiResponse;

    public function __construct(
        private readonly CreateUser $action
    ) {}

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $user = $this->action->handle(
            dto: RegisterUserDTO::fromRequest($request->validated())
        );

        return $this->success(
            message: 'User created successfully',
            code: Response::HTTP_CREATED
        );
    }
}
