<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\Commands\Admin\Users\UpdateUserCommand;
use App\Events\Admin\UserEmailChanged;
use App\Events\Admin\UserUpdated;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class UpdateUser
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger,
    ) {}

    public function handle(UpdateUserCommand $command): void
    {
        try {
            $this->db->transaction(
                callback: function () use ($command): void {

                    $changes = [];

                    $command->targetUser->fill([
                        'email' => $command->email,
                    ]);

                    if ($command->targetUser->isDirty('email')) {
                        $command->targetUser->email_verified_at = null;

                        $changes['email'] = [
                            'old' => $command->targetUser->getOriginal('email'),
                            'new' => $command->email,
                        ];

                        $command->targetUser->save();
                    }

                    $profile = $command->targetUser->profile;

                    $profile->fill(
                        attributes: $command->toProfileAttributes()
                    );

                    if ($profile->isDirty()) {
                        foreach ($profile->getDirty() as $key => $value) {
                            $changes[$key] = [
                                'old' => $profile->getOriginal($key),
                                'new' => $value,
                            ];
                        }

                        $profile->save();
                    }

                    if ($command->targetUser->wasChanged('email')) {
                        UserEmailChanged::dispatch($command->targetUser, $changes['email'], $command->actor, $command->system, $command->metadata);
                    }

                    if (! empty($changes)) {
                        UserUpdated::dispatch($command->targetUser, $changes, $command->actor, $command->system, $command->metadata);
                    }
                }
            );
        } catch (Throwable $exception) {
            $this->logger->critical('Admin User update transaction failed.', [
                'email' => $command->email,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            throw $exception;
        }
    }
}
