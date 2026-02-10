<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Users;

use App\DTOs\Api\V1\Users\VerifyEmailChangeDTO;
use App\Events\Users\UserEmailChanged;
use App\Exceptions\InvalidVerificationRequest;
use App\Models\User;
use Illuminate\Database\DatabaseManager;

final readonly class ConfirmEmailChange
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(VerifyEmailChangeDTO $dto, User $user): void
    {
        if (! $user->pending_email || ! $user->pending_email_token) {
            throw new InvalidVerificationRequest(
                message: 'No pending email change request found.'
            );
        }

        // Constant time comparison to prevent timing attacks
        if (! hash_equals($user->pending_email_token, $dto->token)) {
            throw new InvalidVerificationRequest(
                message: 'Invalid or expired verification token.'
            );
        }

        $this->db->transaction(
            callback: function () use ($user) {

                $oldEmail = $user->email;

                $user->update([
                    'email' => $user->pending_email,
                    'email_verified_at' => now(),
                    'pending_email' => null,
                    'pending_email_token' => null,
                ]);

                $this->db->afterCommit(
                    fn () => UserEmailChanged::dispatch($user, $oldEmail)
                );
            }
        );
    }
}
