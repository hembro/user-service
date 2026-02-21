<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Api\V1\Auth\ResetPasswordData;
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

    public function handle(ResetPasswordData $dto): void
    {
        $status = $this->db->transaction(
            callback: function () use ($dto): string {
                return Password::broker()->reset(
                    credentials: [
                        'email' => $dto->email,
                        'token' => $dto->token,
                        'password' => $dto->password,
                        'password_confirmation' => $dto->password,
                    ],
                    callback: function (User $user, string $password) use ($dto): void {
                        $user->forceFill([
                            'password' => $password,
                        ])->save();

                        $user->tokens()->delete();

                        AuthPasswordReset::dispatch($user, $dto->system);
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
