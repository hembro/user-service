<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\ResetPasswordDTO;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

final readonly class ResetUserPassword
{
    public function handle(ResetPasswordDTO $dto): void
    {
        $status = Password::broker()->reset(
            credentials: [
                'email' => $dto->email,
                'password' => $dto->password,
                'password_confirmation' => $dto->password,
                'token' => $dto->token,
            ],
            callback: function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [$status],
            ]);
        }
    }
}
