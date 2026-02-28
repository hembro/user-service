<?php

declare(strict_types=1);

namespace App\Actions\Admin\Users;

use App\Commands\Admin\Users\ResetPasswordCommand;
use App\Events\Admin\UserPasswordReset;
use Illuminate\Database\DatabaseManager;

final readonly class ResetPassword
{
    public function __construct(
        private DatabaseManager $db,
    ) {}

    public function handle(ResetPasswordCommand $command): void
    {
        $this->db->transaction(
            callback: function () use ($command): void {

                $command->targetUser->update(['password' => $command->password]);

                $command->targetUser->tokens()->delete();

                UserPasswordReset::dispatch($command->targetUser, $command->actor, $command->system, $command->metadata);
            }
        );
    }
}
