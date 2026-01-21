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
        Passport::enablePasswordGrant();
    }

    public function boot(): void
    {
        $this->configurePassport();
        $this->configurePasswordDefaults();
    }

    private function configurePassport(): void
    {
        Passport::tokensCan([
            'pms:read' => 'Read PMS Data',
            'pms:write' => 'Write PMS Data',
            // 'herdin:admin' => 'Admin access to HERDIN',
        ]);

        Passport::enablePasswordGrant();

        Passport::tokensExpireIn(CarbonInterval::minutes(15));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
        Passport::personalAccessTokensExpireIn(CarbonInterval::months(6));
    }

    private function configurePasswordDefaults(): void
    {
        Password::defaults(function () {
            return Password::required()
                ->min(8)
                ->max(255)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised(2);
        });
    }
}
