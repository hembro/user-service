<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Passport\Passport;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Passport::ignoreRoutes();
    }

    public function boot(): void
    {
        $this->configurePassport();
        $this->configurePasswordDefaults();
    }

    private function configurePassport(): void
    {
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(CarbonInterval::minutes(15));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
    }

    private function configurePasswordDefaults(): void
    {
        Password::defaults(
            function (): Password {
                $password = Password::min(8)->max(255);

                return $this->app->environment('production')
                    ? $password->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised(2)
                    : $password;
            }
        );
    }
}
