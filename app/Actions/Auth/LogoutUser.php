<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Api\V1\Auth\LogoutData;
use App\Events\Auth\UserLoggedOut;
use Illuminate\Database\DatabaseManager;

final readonly class LogoutUser
{
    public function __construct(
        private DatabaseManager $db,
    ) {}

    public function handle(LogoutData $dto): void
    {
        $this->db->transaction(
            callback: function () use ($dto): void {

                /** @var \Laravel\Passport\Token|null $accessToken */
                $accessToken = $dto->user->token();

                if (! $accessToken) {
                    return;
                }

                $accessToken->revoke();
                $accessToken->refreshToken?->revoke();

                UserLoggedOut::dispatch($dto->user, $dto->metadata, $dto->system);
            }
        );
    }
}
