<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\Enums\UserStatus;
use App\Exceptions\InvalidVerificationRequest;
use App\Models\User;
use Illuminate\Auth\Events\Verified;

final readonly class VerifyEmail
{
    public function handle(User $user, string $hash): void
    {
        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            throw new InvalidVerificationRequest(
                message: 'The verification link is invalid or has been tampered with.'
            );
        }

        if ($user->hasVerifiedEmail()) {
            throw new InvalidVerificationRequest(
                message: 'Email already verified.'
            );
        }

        if ($user->markEmailAsVerified()) {
            $user->update(['status' => UserStatus::ACTIVE]);
            event(new Verified($user));
        }
    }
}
