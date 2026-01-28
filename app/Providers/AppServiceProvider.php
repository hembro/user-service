<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
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
        $isProduction = $this->app->environment('production');

        $this->configurePassport();
        $this->configurePasswordDefaults($isProduction);
        $this->configureModels($isProduction);
    }

    private function configurePassport(): void
    {
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(CarbonInterval::minutes(15));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));
    }

    private function configurePasswordDefaults(bool $isProduction): void
    {
        Password::defaults(
            function () use ($isProduction): Password {
                $password = Password::min(8)->max(255);

                return $isProduction
                    ? $password->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised(2)
                    : $password;
            }
        );
    }

    private function configureModels(bool $isProduction): void
    {
        Model::shouldBeStrict(! $isProduction);
    }
}
