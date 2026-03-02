<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\System;

use App\Enums\Roles;
use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Systems;
use App\Enums\Titles;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;

final class LookupsController
{
    use HasApiResponse;

    public function __invoke(): JsonResponse
    {
        $data = Cache::rememberForever('api.v1.system.lookups', function (): array {

            $rolesBySystem = [];
            foreach (Systems::cases() as $system) {
                $rolesBySystem[$system->value] = Roles::forSystem($system, true);
            }

            return [
                'titles' => Titles::options(),
                'suffixes' => Suffix::options(),
                'sexes' => Sex::options(),
                'statuses' => UserStatus::options(),
                'systems' => Systems::options(),
                'roles' => $rolesBySystem,
            ];
        });

        return $this->success(
            data: $data,
            message: 'System lookups retrieved successfully.'
        );
    }
}
