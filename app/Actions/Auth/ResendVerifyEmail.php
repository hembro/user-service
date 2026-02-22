<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\ResendVerifyEmailCommand;
use App\Events\Auth\VerificationLinkRequested;
use App\Exceptions\Auth\InvalidVerificationRequest;
use App\Models\User;
use App\Services\Auth\VerificationLinkGenerator;
use Illuminate\Database\DatabaseManager;

final readonly class ResendVerifyEmail
{
    public function __construct(
        private DatabaseManager $db,
        private VerificationLinkGenerator $linkGenerator,
    ) {}

    public function handle(ResendVerifyEmailCommand $command): void
    {
        $user = User::query()->where('email', $command->email)->first();

        if (! $user) {
            return;
        }

        if ($user->hasVerifiedEmail()) {
            throw new InvalidVerificationRequest('Email already verified.');
        }

        $verificationUrl = $this->linkGenerator->generate($user);

        $this->db->transaction(
            callback: function () use ($user, $verificationUrl, $command): void {
                VerificationLinkRequested::dispatch($user, $verificationUrl, $command->system);
            }
        );
    }
}
