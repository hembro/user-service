<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\ForgotPasswordCommand;
use App\Events\Auth\PasswordResetRequested;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Password;

final readonly class SendResetPasswordLink
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(ForgotPasswordCommand $command): void
    {
        $user = User::query()->where('email', $command->email)->first();

        $this->db->transaction(
            callback: function () use ($user, $command): void {
                $token = Password::createToken($user);
                PasswordResetRequested::dispatch($user, $token, $command->system, $command->metadata);
            }
        );
    }
}
