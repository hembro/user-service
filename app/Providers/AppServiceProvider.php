<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\Roles;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Date;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->configureDefaults($isProduction);
        $this->configureModels($isProduction);
        $this->configureEmailVerification();
        $this->configureRateLimiting($isProduction);
    }

    private function configurePassport(): void
    {
        // Enables logging-in with access and refresh tokens.
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(CarbonInterval::minutes(15));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));

        // Enables logging-in with PATs for impersonation only.
        Passport::personalAccessTokensExpireIn(CarbonInterval::minutes(30));

        Passport::tokensCan(Roles::getPassportScopes());
    }

    private function configureDefaults(bool $isProduction): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands($isProduction);

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
                ->withPath("/auth/email/verify/{$notifiable->getKey()}/" . sha1($notifiable->getEmailForVerification()))
                ->withQuery($queryParams)
                ->toStringable()
                ->toString();
        });
    }

    private function configureRateLimiting(bool $isProduction): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth.login', function (Request $request) use ($isProduction) {
            return Limit::perMinute($isProduction ? 5 : 100)->by($request->input('email') ?? $request->ip());
        });

        RateLimiter::for('auth.api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth.email', function (Request $request) use ($isProduction) {
            return Limit::perMinute($isProduction ? 1 : 100)->by($request->ip());
        });

        RateLimiter::for('auth.register', function (Request $request) use ($isProduction) {
            return Limit::perHour($isProduction ? 5 : 100)->by($request->ip());
        });
    }
}
