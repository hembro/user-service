<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\System;

use App\Enums\Permissions;
use App\Enums\Roles;
use App\Enums\Sex;
use App\Enums\SocialProviders;
use App\Enums\Suffix;
use App\Enums\Systems;
use App\Enums\Titles;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;

final class LookupsController
{
    use HasApiResponse;

    public function __invoke(): JsonResponse
    {
        $data = Cache::rememberForever('api.v1.system.lookups', function (): array {

            $rolesBySystem = [];
            foreach (Systems::cases() as $system) {
                $systemRoles = Roles::forSystem($system, false);

                $rolesBySystem[$system->value] = array_map(fn (Roles $role) => [
                    'label' => $role->description() ?? $role->name,
                    'value' => $role->value,
                ], $systemRoles);
            }

            return [
                // Demographics
                'titles' => Titles::options(),
                'suffixes' => Suffix::options(),
                'sexes' => Sex::options(),

                // System & Access
                'systems' => Systems::options(),
                'roles' => $rolesBySystem,
                'permissions' => Permissions::options(),

                // State & Auth
                'social_providers' => SocialProviders::options(),
                'user_statuses' => collect(UserStatus::cases())->map(fn (UserStatus $status) => [
                    'label' => Str::headline($status->value),
                    'value' => $status->value,
                ]),
            ];
        });

        return $this->success(
            data: $data,
            message: 'System lookups retrieved successfully.'
        );
    }
}
