<?php

declare(strict_types=1);

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
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
        $this->configurePasswordDefaults($isProduction);
        $this->configureModels($isProduction);
        $this->configureEmailVerification();
        $this->configureRateLimiting($isProduction);
    }

    private function configurePassport(): void
    {
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(CarbonInterval::minutes(15));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));

        Passport::tokensCan([
            'pms.admin' => 'PMS: Admin for the whole Information System.',
            'pms.division-admin' => 'PMS: Admin for a specific division within the Information System.',
            'pms.division-chief' => 'PMS: Receive, monitor and reviews the proposal assigned to its division and then assigns it accordingly to senior officer/program manager/project officer.',
            'pms.senior-officer' => 'PMS: Receive, monitor and reviews the proposal assigned to its division and then assigns it accordingly to program manager/project officer. Senior officer can also be assigned to a proposal by the division chief.',
            'pms.project-officer' => 'PMS: Process and reviews the concept proposal and full blown proposal assigned to them until its completion.',
            'pms.program-manager' => 'PMS: Receive, monitor and reviews the proposal assigned to them by their senior officer or division chief, and then assigns it accordingly to project officer.',
            'pms.planning-officer' => 'PMS: Oversees and monitors the proposal that comes into their division. They can also produce a report from the proposals.',
            'pms.records-officer' => 'PMS: Records the concept proposal and full blown proposal that the Information System receives.',
            'pms.technical-reviewer' => 'PMS: Technical Reviewer or the Consultant, reviews the proposal assigned to them when deemed necessary for a 3rd party technical review.',
            'pms.proponent' => 'PMS: The user that submits a proposal.',

            'herdin.admin' => 'HERDIN: System Administrator',
            'herdin.user' => 'HERDIN: Standard User',

            'phrr.admin' => 'PHRR: System Administrator',
            'phrr.user' => 'PHRR: Standard User',
        ]);
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

    private function configureRateLimiting(bool $isProduction): void
    {
        RateLimiter::for('auth.login', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip()),
                Limit::perMinute(5)->by($request->input('email')),
            ];
        });

        RateLimiter::for('auth.api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth.email', function (Request $request) {
            return Limit::perMinute(1)->by($request->ip());
        });

        RateLimiter::for('auth.register', function (Request $request) use ($isProduction) {
            return Limit::perHour($isProduction ? 5 : 100)->by($request->ip());
        });
    }
}
