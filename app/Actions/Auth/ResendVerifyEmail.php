<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\ResendVerifyEmailCommand;
use App\Events\Auth\VerificationLinkRequested;
use App\Exceptions\Auth\InvalidVerificationRequest;
use App\Models\User;
use App\Services\Auth\VerificationLinkGenerator;

final readonly class ResendVerifyEmail
{
    public function __construct(
        private VerificationLinkGenerator $linkGenerator
    ) {}

    public function handle(ResendVerifyEmailCommand $command): void
    {
        $user = User::query()
            ->with('profile')
            ->where('email', $command->email)
            ->first();

        if (! $user) {
            return;
        }

        if ($user->hasVerifiedEmail()) {
            throw new InvalidVerificationRequest('Email already verified.');
        }

        $verificationUrl = $this->linkGenerator->generate($user);

        VerificationLinkRequested::dispatch($user, $verificationUrl, $command->system);
    }
}
