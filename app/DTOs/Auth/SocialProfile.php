<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use App\Enums\SocialProviders;
use Laravel\Socialite\Contracts\User as SocialUser;

final readonly class SocialProfile
{
    public function __construct(
        public SocialProviders $provider,
        public string $providerId,
        public string $email,
        public string $firstName,
        public string $lastName,
        public ?string $avatarPath,
    ) {}

    public static function fromSocialite(SocialProviders $provider, SocialUser $socialUser): self
    {
        $nameParts = explode(' ', $socialUser->getName() ?? 'User');
        $firstName = array_shift($nameParts);
        $lastName = count($nameParts) > 0 ? implode(' ', $nameParts) : 'Unknown';

        return new self(
            provider: $provider,
            providerId: $socialUser->getId(),
            email: $socialUser->getEmail(),
            firstName: $firstName,
            lastName: $lastName,
            avatarPath: $socialUser->getAvatar(),
        );
    }
}
