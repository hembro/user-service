<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\ForgotPasswordData;
use App\Events\Auth\PasswordResetRequested;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Password;

final readonly class SendResetLink
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(ForgotPasswordData $dto): void
    {
        $user = User::query()->where('email', $dto->email)->first();

        $this->db->transaction(
            callback: function () use ($user, $dto): void {
                $token = Password::createToken($user);
                PasswordResetRequested::dispatch($user, $token, $dto->system);
            }
        );
    }
}
