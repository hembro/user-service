<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\URL;

final readonly class VerificationLinkGenerator
{
    public function generate(User $user): string
    {
        return URL::temporarySignedRoute(
            name: 'api.v1.auth.email.verification.verify',
            expiration: now()->addMinutes((int) config('auth.verification.expire', 60)),
            parameters: [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );
    }
}
