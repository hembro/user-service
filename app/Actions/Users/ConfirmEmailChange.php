<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Commands\Users\VerifyEmailChangeCommand;
use App\Events\Users\UserEmailChanged;
use App\Exceptions\Auth\InvalidVerificationRequest;
use Illuminate\Database\DatabaseManager;
use Psr\Log\LoggerInterface;

final readonly class ConfirmEmailChange
{
    public function __construct(
        private DatabaseManager $db,
        private LoggerInterface $logger
    ) {}

    public function handle(VerifyEmailChangeCommand $command): void
    {
        if (! $command->user->pending_email || ! $command->user->pending_email_token) {
            throw new InvalidVerificationRequest('No pending email change request found.');
        }

        // Constant time comparison to prevent timing attacks
        if (! hash_equals($command->user->pending_email_token, $command->token)) {

            $this->logger->warning(
                'Email change token tampering or expiration detected.',
                [
                    'user_id' => $command->user->id,
                    'attempted_token' => $command->token,
                ]
            );

            throw new InvalidVerificationRequest('Invalid or expired verification token.');
        }

        $this->db->transaction(
            callback: function () use ($command) {

                $oldEmail = $command->user->email;

                $command->user->update([
                    'email' => $command->user->pending_email,
                    'email_verified_at' => now(),
                    'pending_email' => null,
                    'pending_email_token' => null,
                ]);

                UserEmailChanged::dispatch($command->user, $oldEmail, $command->system, $command->metadata);
            }
        );
    }
}
