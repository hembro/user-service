<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\ResetPasswordCommand;
use App\Events\Auth\PasswordReset as AuthPasswordReset;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

final readonly class ResetUserPassword
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(ResetPasswordCommand $command): void
    {
        $status = $this->db->transaction(
            callback: function () use ($command): string {
                return Password::broker()->reset(
                    credentials: [
                        'email' => $command->email,
                        'token' => $command->token,
                        'password' => $command->password,
                        'password_confirmation' => $command->password,
                    ],
                    callback: function (User $user, string $password) use ($command): void {
                        $user->forceFill([
                            'password' => $password,
                        ])->save();

                        $user->tokens()->delete();

                        AuthPasswordReset::dispatch($user, $command->system, $command->metadata);
                    }
                );
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [$status],
            ]);
        }
    }
}
