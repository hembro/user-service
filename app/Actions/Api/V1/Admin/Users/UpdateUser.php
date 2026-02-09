<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\UpdateUserDTO;
use App\Events\Admin\UserUpdated;
use App\Models\User;
use App\Notifications\VerifyEmailChangedByAdmin;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

final readonly class UpdateUser
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger,
    ) {}

    public function handle(UpdateUserDTO $dto, User $user, User $admin): void
    {
        $this->db->transaction(
            callback: function () use ($user, $dto, $admin): void {

                $changes = [];

                $user->fill([
                    'email' => $dto->email,
                ]);

                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;

                    $changes['email'] = [
                        'old' => $user->getOriginal('email'),
                        'new' => $dto->email,
                    ];

                    $user->save();
                }

                $profile = $user->profile;

                $profile->fill(
                    attributes: $dto->toAttributes()
                );

                if ($profile->isDirty()) {
                    foreach ($profile->getDirty() as $key => $value) {
                        $changes["profile.{$key}"] = [
                            'old' => $profile->getOriginal($key),
                            'new' => $value,
                        ];
                    }

                    $profile->save();
                }

                if ($user->wasChanged('email')) {
                    $this->db->afterCommit(
                        fn () => $user->notify(
                            instance: new VerifyEmailChangedByAdmin(
                                adminName: $admin->profile?->full_name ?? 'Administrator'
                            )
                        )
                    );
                }

                if (! empty($changes)) {

                    $this->logger->info('admin user update initiated', [
                        'admin_id' => $admin->id,
                        'target_user_id' => $user->id,
                        'changes_count' => count($changes),
                    ]);

                    $this->db->afterCommit(
                        fn () => UserUpdated::dispatch($admin, $user, $changes)
                    );
                }
            }
        );
    }
}
