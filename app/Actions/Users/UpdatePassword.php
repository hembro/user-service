<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Commands\Users\UpdatePasswordCommand;
use App\Events\Users\UserPasswordUpdated;
use Illuminate\Database\DatabaseManager;

final readonly class UpdatePassword
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(UpdatePasswordCommand $command): void
    {
        $this->db->transaction(
            callback: function () use ($command): void {

                $command->user->update([
                    'password' => $command->newPassword,
                ]);

                $currentAccessToken = $command->user->currentAccessToken();

                if ($currentAccessToken !== null) {
                    $command->user->tokens()->where('id', '!=', $currentAccessToken->id)->delete();
                }

                UserPasswordUpdated::dispatch($command->user, $command->system, $command->metadata);
            }
        );
    }
}
