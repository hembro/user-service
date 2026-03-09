<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin\Users;

use App\Actions\Admin\Users\FetchUsersForSystem;
use App\Commands\Admin\Users\IndexUserCommand;
use App\Http\Requests\Api\V1\Admin\Users\IndexRequest as AdminIndexRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use Illuminate\Http\JsonResponse;

final class IndexController
{
    public function __construct(
        private readonly FetchUsersForSystem $action
    ) {}

    public function __invoke(AdminIndexRequest $request): JsonResponse
    {
        $users = $this->action->handle(
            IndexUserCommand::fromRequest($request)
        );

        return JsonResponse::paginated(
            resource: UserResource::collection($users)
        );
    }
}
