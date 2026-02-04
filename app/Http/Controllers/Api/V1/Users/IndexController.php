<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\Actions\Api\V1\Users\FetchUsersForSystem;
use App\DTOs\Api\V1\Users\UserIndexDTO;
use App\Http\Requests\Api\V1\Users\IndexRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;

final class IndexController
{
    use HasApiResponse;

    public function __construct(
        private readonly FetchUsersForSystem $action
    ) {}

    public function __invoke(IndexRequest $request): JsonResponse
    {
        $users = $this->action->handle(
            dto: UserIndexDTO::fromRequest($request)
        );

        return $this->paginated(
            resource: UserResource::collection($users)
        );
    }
}
