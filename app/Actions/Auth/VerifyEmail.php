<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\VerifyEmailCommand;
use App\Events\Auth\UserVerified;
use App\Exceptions\Auth\InvalidVerificationRequest;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use jeremyaliparo\IntegrationSchemas\Enums\UserStatus;
use Psr\Log\LoggerInterface;

final readonly class VerifyEmail
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function handle(VerifyEmailCommand $command): void
    {
        $user = User::query()
            ->with('profile')
            ->findOrFail($command->id);

        if (! hash_equals($command->hash, sha1($user->getEmailForVerification()))) {

            $this->logger->warning(
                'Email verification hash tampering detected.',
                [
                    'user_id' => $user->id,
                    'provided_hash' => $command->hash,
                ]
            );

            throw new InvalidVerificationRequest('The verification link is invalid or has been tampered with.');
        }

        if ($user->hasVerifiedEmail()) {
            throw new InvalidVerificationRequest('Email already verified.');
        }

        $this->db->transaction(
            callback: function () use ($user, $command): void {

                if (! $user->markEmailAsVerified()) {
                    throw new InvalidVerificationRequest('Email could not be verified due to a system error.');
                }

                $user->update(['status' => UserStatus::ACTIVE]);

                UserVerified::dispatch($user, $command->system, $command->metadata);
            }
        );
    }
}
