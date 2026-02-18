<?php

declare(strict_types=1);

namespace App\Actions\Api\V1\Auth;

use App\DTOs\Api\V1\Auth\SocialRedirectDTO;
use Laravel\Socialite\Facades\Socialite;

final readonly class GetSocialRedirectUrl
{
    public function handle(SocialRedirectDTO $dto): string
    {
        return Socialite::driver($dto->provider->value)
            ->stateless()
            ->redirect()
            ->getTargetUrl();
    }
}
