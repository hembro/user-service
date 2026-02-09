<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\System;

use App\Enums\Roles;
use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Systems;
use App\Enums\Titles;
use App\Enums\UserStatus;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

final class LookupsController
{
    use HasApiResponse;

    public function __invoke(): JsonResponse
    {
        $data = Cache::rememberForever('api.v1.system.lookups', function () {
            return [
                'titles' => Titles::options(),
                'suffixes' => Suffix::options(),
                'sexes' => Sex::options(),
                'statuses' => UserStatus::options(),
                'systems' => Systems::options(),

                'roles' => [
                    'pms' => $this->getRolesFor(Systems::PMS),
                    'herdin' => $this->getRolesFor(Systems::HERDIN),
                    'phrr' => $this->getRolesFor(Systems::PHRR),
                ],
            ];
        });

        return $this->success(
            data: $data,
            message: 'System lookups retrieved successfully.'
        );
    }

    private function getRolesFor(Systems $system): array
    {
        return array_map(fn (Roles $role) => [
            'label' => $role->label(),
            'value' => $role->value,
        ], Roles::forSystem($system));
    }
}
