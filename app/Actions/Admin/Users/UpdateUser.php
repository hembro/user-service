<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\Commands\Admin\Users\UpdateUserCommand;
use App\Events\Admin\UserEmailChanged;
use App\Events\Admin\UserUpdated;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

final readonly class UpdateUser
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger,
    ) {}

    public function handle(UpdateUserCommand $command): void
    {
        $this->db->transaction(
            callback: function () use ($command): void {

                $changes = [];

                $command->targetuser->fill([
                    'email' => $command->email,
                ]);

                if ($command->targetuser->isDirty('email')) {
                    $command->targetuser->email_verified_at = null;

                    $changes['email'] = [
                        'old' => $command->targetuser->getOriginal('email'),
                        'new' => $command->email,
                    ];

                    $command->targetuser->save();
                }

                $profile = $command->targetuser->profile;

                $profile->fill(
                    attributes: $command->toProfileAttributes()
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

                if ($command->targetuser->wasChanged('email')) {
                    UserEmailChanged::dispatch($command->targetuser, $command->actor, $command->system);
                }

                if (! empty($changes)) {
                    UserUpdated::dispatch($command->actor, $command->targetuser, $changes, $command->system);
                }
            }
        );
    }
}
