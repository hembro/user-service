<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\UpdateUserDTO;
use App\Enums\Roles;
use App\Events\AdminUpdatedUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

final readonly class UpdateUser
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function handle(User $user, UpdateUserDTO $dto, User $admin): User
    {
        /** @var Roles $roleEnum */
        foreach ($dto->roles as $roleEnum) {
            if ($roleEnum->system() !== $dto->system) {
                throw new InvalidArgumentException(
                    message: "Security Violation: Role '{$roleEnum->value}' does not belong to system '{$dto->system->value}'."
                );
            }
        }

        return $this->db->transaction(function () use ($user, $dto, $admin) {

            $changes = [];

            // 1. Get all the current roles of the user for the current system
            $currentSystemRoles = $user->roles
                ->filter(fn (Role $roleModel) => Roles::tryFrom($roleModel->name)?->system() === $dto->system);

            // 2. Determine the new roles
            $newRoleNames = array_map(fn (Roles $enum) => $enum->value, $dto->roles);

            // 3. Check for Changes
            $oldNames = $currentSystemRoles->pluck('name')->sort()->values()->all();
            $sortedNewNames = collect($newRoleNames)->sort()->values()->all();

            if ($oldNames !== $sortedNewNames) {

                if ($currentSystemRoles->isNotEmpty()) {
                    $user->removeRole($currentSystemRoles);
                }

                $user->assignRole($dto->roles);

                $changes['roles'] = [
                    'old' => implode(', ', $oldNames),
                    'new' => implode(', ', $sortedNewNames),
                ];
            }

            $user->fill([
                'email' => $dto->email,
            ]);

            if ($user->isDirty()) {
                foreach ($user->getDirty() as $key => $value) {
                    $changes[$key] = [
                        'old' => $user->getOriginal($key),
                        'new' => $value,
                    ];
                }

                $user->save();
            }

            $profile = $user->profile;

            $profile->fill([
                'title' => $dto->title?->value,
                'first_name' => $dto->firstName,
                'last_name' => $dto->lastName,
                'middle_name' => $dto->middleName,
                'suffix' => $dto->suffix?->value,
                'sex' => $dto->sex->value,
                'mobile_number' => $dto->mobileNumber,
                'preferences' => $dto->preferences,
            ]);

            if ($profile->isDirty()) {
                foreach ($profile->getDirty() as $key => $value) {
                    $changes["profile.{$key}"] = [
                        'old' => $profile->getOriginal($key),
                        'new' => $value,
                    ];
                }

                $profile->save();
            }

            if (! empty($changes)) {
                Cache::tags([
                    'users_index',
                    "users_index.{$dto->system->value}",
                ])->flush();

                $this->logger->info('admin user update initiated', [
                    'admin_id' => $admin->id,
                    'target_user_id' => $user->id,
                    'changes_count' => count($changes),
                ]);

                AdminUpdatedUser::dispatch($admin, $user, $changes);
            }

            return $user->refresh();
        });
    }
}
