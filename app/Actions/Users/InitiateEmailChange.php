<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Commands\Users\InitiateEmailChangeCommand;
use App\Events\Users\UserEmailChangeRequested;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;

final readonly class InitiateEmailChange
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(InitiateEmailChangeCommand $command): void
    {
        $this->db->transaction(
            callback: function () use ($command) {

                $token = Str::random(64);

                $command->user->update([
                    'pending_email' => $command->email,
                    'pending_email_token' => $token,
                ]);

                UserEmailChangeRequested::dispatch($command->user, $token, $command->email, $command->system, $command->metadata);
            }
        );
    }
}
