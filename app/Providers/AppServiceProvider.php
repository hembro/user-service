<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\Enums\Roles;
use App\OAuth\Grants\SystemVerifiedGrant;
use App\Services\Auth\DeviceTrustService;
use App\Services\Auth\TokenIssuer;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use Date;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Passport::ignoreRoutes();
        $this->bindContracts();
    }

    public function boot(): void
    {
        $isProduction = $this->app->environment('production');

        $this->configurePassport();
        $this->configureDefaults($isProduction);
        $this->configureModels($isProduction);
        $this->configureRateLimiting($isProduction);
        $this->registerCustomGrants();
        $this->bindServices();
    }

    private function configurePassport(): void
    {
        // Enables logging-in with access and refresh tokens.
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(CarbonInterval::minutes(15));
        Passport::refreshTokensExpireIn(CarbonInterval::days(30));

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

    private function registerCustomGrants(): void
    {
        /** @var AuthorizationServer $server */
        $server = $this->app->make(AuthorizationServer::class);

        /** @var RefreshTokenRepository $refreshTokenRepository */
        $refreshTokenRepository = $this->app->make(RefreshTokenRepository::class);

        $verifiedGrant = new SystemVerifiedGrant($refreshTokenRepository);
        $verifiedGrant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

        $server->enableGrantType(
            grantType: $verifiedGrant,
            accessTokenTTL: Passport::tokensExpireIn()
        );
    }

    private function bindServices(): void
    {
        $this->app->bind(TokenIssuer::class, function ($app) {
            return new TokenIssuer(
                server: $app->make(AuthorizationServer::class),
                systemClients: config('services.passport.frontend_clients'),
                internalSignature: config('app.key')
            );
        });
    }

    private function bindContracts(): void
    {
        // For testing purposes
        $this->app->bind(DeviceTrustVerifier::class, DeviceTrustService::class);
    }
}
