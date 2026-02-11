<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\Enums\Roles;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Database\DatabaseManager;

final class SyncSystemRoles
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(User $user, Systems $system, array $newRoles): ?array
    {
        return $this->db->transaction(
            callback: function () use ($user, $system, $newRoles) {

                $oldRoleValues = $user->roles()
                    ->getQuery()
                    ->where('name', 'like', "{$system->value}.%")
                    ->pluck('name')
                    ->toArray();
                sort($oldRoleValues);

                $newRoleValues = array_unique(
                    array_map(fn (Roles $r) => $r->value, $newRoles)
                );
                sort($newRoleValues);

                if ($oldRoleValues === $newRoleValues) {
                    return null;
                }

                if (! empty($oldRoleValues)) {
                    $user->removeRole($oldRoleValues);
                }

                if (! empty($newRoleValues)) {
                    $user->assignRole($newRoleValues);
                }

                return [
                    'old' => $oldRoleValues,
                    'new' => $newRoleValues,
                ];
            }
        );
    }
}
