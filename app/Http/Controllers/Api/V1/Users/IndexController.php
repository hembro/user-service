<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Users;

use App\DTOs\Api\V1\Users\UserIndexDTO;
use App\Enums\Roles;
use App\Http\Requests\Api\V1\Users\IndexRequest;
use App\Http\Resources\Api\V1\Users\UserResource;
use App\Models\User;
use App\Traits\HasApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final class IndexController
{
    use HasApiResponse;

    public function __invoke(IndexRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load('profile');
        $userIndexDto = UserIndexDTO::fromRequest($request);

        if (! $user->belongsToSystem($userIndexDto->system)) {
            return $this->error(
                message: "You are not authorized to view {$userIndexDto->system->value} users.",
                code: Response::HTTP_FORBIDDEN
            );
        }

        $users = Cache::tags(['users_index', "users_index.{$userIndexDto->system->value}"])
            ->remember(
                key: $userIndexDto->generateCacheKey(),
                ttl: now()->addHour(1),
                callback: fn () => User::query()

                    ->with(['profile', 'roles', 'permissions'])

                    // A. Ensures PMS Admins NEVER see HERDIN users in the list.
                    ->role(Roles::forSystem($userIndexDto->system, true))

                    // B. Search logic
                    ->when(
                        $userIndexDto->search,
                        fn (Builder $query, string $search) => $query->where(function (Builder $q) use ($search) {
                            $q->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                    )

                    // C. Filter by Specific Role
                    // e.g. ?role=pms.project-officer
                    ->when(
                        $userIndexDto->role,
                        fn (Builder $query, string $role) => $query->role($role)
                    )

                    // D. Sorting & Pagination
                    ->latest()
                    ->paginate($userIndexDto->perPage)
            );

        return $this->success(
            data: UserResource::collection($users)
        );
    }
}
