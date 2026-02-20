<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Users;

use App\DTOs\Api\V1\Users\VerifyEmailChangeData;
use App\Events\Users\UserEmailChanged;
use App\Exceptions\InvalidVerificationRequest;
use Illuminate\Database\DatabaseManager;

final readonly class ConfirmEmailChange
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(VerifyEmailChangeData $dto): void
    {
        if (! $dto->user->pending_email || ! $dto->user->pending_email_token) {
            throw new InvalidVerificationRequest('No pending email change request found.');
        }

        // Constant time comparison to prevent timing attacks
        if (! hash_equals($dto->user->pending_email_token, $dto->token)) {
            throw new InvalidVerificationRequest('Invalid or expired verification token.');
        }

        $this->db->transaction(
            callback: function () use ($dto) {

                $oldEmail = $dto->user->email;

                $dto->user->update([
                    'email' => $dto->user->pending_email,
                    'email_verified_at' => now(),
                    'pending_email' => null,
                    'pending_email_token' => null,
                ]);

                UserEmailChanged::dispatch($dto->user, $oldEmail, $dto->system);
            }
        );
    }
}
