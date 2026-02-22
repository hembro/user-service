<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Commands\Auth\SocialRedirectCommand;
use Laravel\Socialite\Facades\Socialite;

final readonly class GetSocialRedirectUrl
{
    public function handle(SocialRedirectCommand $command): string
    {
        return Socialite::driver($command->provider->value)
            ->stateless()
            ->redirect()
            ->getTargetUrl();
    }
}
