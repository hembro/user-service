<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\DTOs\Api\V1\Users\InitiateEmailChangeData;
use App\Events\Users\UserEmailChangeRequested;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;

final readonly class InitiateEmailChange
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(InitiateEmailChangeData $dto): void
    {
        $this->db->transaction(
            callback: function () use ($dto) {

                $token = Str::random(64);

                $dto->user->update([
                    'pending_email' => $dto->email,
                    'pending_email_token' => $token,
                ]);

                UserEmailChangeRequested::dispatch($dto->user, $token, $dto->email, $dto->system);
            }
        );
    }
}
