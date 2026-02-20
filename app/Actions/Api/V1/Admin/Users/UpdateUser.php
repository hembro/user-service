<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Admin\Users;

use App\DTOs\Api\V1\Admin\Users\UpdateUserData;
use App\Events\Admin\UserUpdated;
use App\Notifications\VerifyEmailChangedByAdmin;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

final readonly class UpdateUser
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger,
    ) {}

    public function handle(UpdateUserData $dto): void
    {
        $this->db->transaction(
            callback: function () use ($dto): void {

                $changes = [];

                $dto->targetuser->fill([
                    'email' => $dto->email,
                ]);

                if ($dto->targetuser->isDirty('email')) {
                    $dto->targetuser->email_verified_at = null;

                    $changes['email'] = [
                        'old' => $dto->targetuser->getOriginal('email'),
                        'new' => $dto->email,
                    ];

                    $dto->targetuser->save();
                }

                $profile = $dto->targetuser->profile;

                $profile->fill(
                    attributes: $dto->toProfileAttributes()
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

                if ($dto->targetuser->wasChanged('email')) {
                    $this->db->afterCommit(
                        fn () => $dto->targetuser->notify(
                            instance: new VerifyEmailChangedByAdmin(
                                adminName: $admin->profile?->full_name ?? 'Administrator',
                                system: $dto->system
                            )
                        )
                    );
                }

                if (! empty($changes)) {

                    $this->logger->debug('admin user update initiated', [
                        'admin_id' => (string) $dto->actor->id,
                        'target_user_id' => (string) $dto->targetuser->id,
                        'changes_count' => count($changes),
                    ]);

                    UserUpdated::dispatch($dto->actor, $dto->targetuser, $changes, $dto->system);
                }
            }
        );
    }
}
