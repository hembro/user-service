<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\ResendVerifyEmailData;
use App\Events\Auth\VerificationLinkRequested;
use App\Exceptions\InvalidVerificationRequest;
use App\Models\User;
use App\Services\Auth\VerificationLinkGenerator;
use Illuminate\Database\DatabaseManager;

final readonly class ResendVerifyEmail
{
    public function __construct(
        private DatabaseManager $db,
        private VerificationLinkGenerator $linkGenerator,
    ) {}

    public function handle(ResendVerifyEmailData $dto): void
    {
        $user = User::query()->where('email', $dto->email)->first();

        if (! $user) {
            return;
        }

        if ($user->hasVerifiedEmail()) {
            throw new InvalidVerificationRequest('Email already verified.');
        }

        $verificationUrl = $this->linkGenerator->generate($user);

        $this->db->transaction(
            callback: function () use ($user, $verificationUrl, $dto): void {
                VerificationLinkRequested::dispatch($user, $verificationUrl, $dto->system);
            }
        );
    }
}
