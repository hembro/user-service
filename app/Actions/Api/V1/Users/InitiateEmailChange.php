<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Users;

use App\DTOs\Api\V1\Users\InitiateEmailChangeDTO;
use App\Events\Users\UserEmailChangeRequested;
use App\Models\User;
use App\Notifications\VerifyNewEmail;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Str;

final readonly class InitiateEmailChange
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(InitiateEmailChangeDTO $dto, User $user): void
    {
        $this->db->transaction(
            callback: function () use ($user, $dto) {

                $token = Str::random(64);

                $user->update([
                    'pending_email' => $dto->email,
                    'pending_email_token' => $token,
                ]);

                $this->db->afterCommit(
                    function () use ($dto, $user, $token) {

                        UserEmailChangeRequested::dispatch($user, $dto->email);

                        $user->notify(
                            instance: new VerifyNewEmail($token, $dto->system)
                        );
                    }
                );
            }
        );
    }
}
