<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\LogoutCommand;
use App\Events\Auth\UserLoggedOut;
use Illuminate\Database\DatabaseManager;

final readonly class LogoutUser
{
    public function __construct(
        private DatabaseManager $db,
    ) {}

    public function handle(LogoutCommand $command): void
    {
        $this->db->transaction(
            callback: function () use ($command): void {

                /** @var \Laravel\Passport\Token|null $accessToken */
                $accessToken = $command->user->token();

                if (! $accessToken) {
                    return;
                }

                $accessToken->revoke();
                $accessToken->refreshToken?->revoke();

                UserLoggedOut::dispatch($command->user, $command->system);
            }
        );
    }
}
