<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Uri;
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
        $this->configureEmailVerification();
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

    private function configureEmailVerification(): void
    {
        VerifyEmail::createUrlUsing(function (object $notifiable): string {
            $apiUrl = URL::temporarySignedRoute(
                name: 'api.v1.auth.verification.verify',
                expiration: now()->addMinutes((int) config('auth.verification.expire', 60)),
                parameters: [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            $components = parse_url($apiUrl);
            parse_str($components['query'] ?? '', $queryParams);

            return Uri::of(config('app.frontend.url'))
                ->withPath("/auth/verify/{$notifiable->getKey()}/" . sha1($notifiable->getEmailForVerification()))
                ->withQuery($queryParams)
                ->toStringable()
                ->toString();
        });
    }
}
