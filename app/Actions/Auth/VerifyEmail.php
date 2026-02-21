<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DTOs\Api\V1\Auth\VerifyEmailData;
use App\Enums\UserStatus;
use App\Events\Auth\UserVerified;
use App\Exceptions\InvalidVerificationRequest;
use App\Models\User;
use Illuminate\Database\DatabaseManager;

final readonly class VerifyEmail
{
    public function __construct(
        private DatabaseManager $db
    ) {}

    public function handle(VerifyEmailData $dto): void
    {
        $user = User::query()->findOrFail($dto->id);

        if (! hash_equals($dto->hash, sha1($user->getEmailForVerification()))) {
            throw new InvalidVerificationRequest('The verification link is invalid or has been tampered with.');
        }

        if ($user->hasVerifiedEmail()) {
            throw new InvalidVerificationRequest('Email already verified.');
        }

        $this->db->transaction(
            callback: function () use ($user, $dto): void {

                if (! $user->markEmailAsVerified()) {
                    throw new InvalidVerificationRequest('Email could not be verified due to a system error.');
                }

                $user->update(['status' => UserStatus::ACTIVE]);

                UserVerified::dispatch($user, $dto->system);
            }
        );
    }
}
