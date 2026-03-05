<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Enums\Auth\ChallengeType;
use App\Enums\Roles;
use App\Enums\Systems;
use App\Events\Auth\UserLoggedIn;
use App\Models\User;
use App\Models\UserProfile;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;
use Laravel\Passport\Client;
use PragmaRX\Google2FA\Google2FA;

use function Pest\Laravel\postJson;
use function Pest\Laravel\seed;
use function Pest\Laravel\travel;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // 1. Setup Passport Client
    $plainSecret = 'secret';
    $client = Client::forceCreate([
        'name' => 'Test Client',
        'secret' => Hash::make($plainSecret),
        'provider' => 'users',
        'redirect_uris' => [],
        'grant_types' => ['password', 'refresh_token'],
        'revoked' => false,
    ]);

    Config::set('services.passport.frontend_clients.pms', [
        'client_id' => $client->id,
        'client_secret' => $plainSecret,
    ]);

    // Helper to generate a valid Secret
    $this->google2fa = new Google2FA();
    $this->userSecret = $this->google2fa->generateSecretKey();

    seed(RoleAndPermissionSeeder::class);
});

describe('2FA Feature: The Happy Path', function (): void {

    it('triggers a 2FA challenge during login if enabled', function (): void {
        // Arrange
        $user = User::factory()->create([
            'password' => 'password',
            'status' => UserStatus::ACTIVE,
            'two_factor_secret' => encrypt($this->userSecret),
            'two_factor_confirmed_at' => now(),
        ]);

        $user->assignRole(Roles::PMS_PROPONENT);

        // Act
        $response = postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ], ['X-Source-System' => Systems::PMS->value]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', ChallengeType::TWO_FACTOR->message())
            ->assertJsonStructure([
                'data' => [
                    'challenge_id', // The temporary ID linking session
                    'challenge_type',
                ],
            ]);

        // Ensure NO tokens are issued yet
        $response->assertJsonMissing(['access_token']);
        $response->assertCookieMissing(config('cookie.refresh_token.name'));

        seed(RoleAndPermissionSeeder::class);
    });

    it('accepts a valid TOTP code and issues tokens', function (): void {
        Event::fake();

        // Arrange
        $user = User::factory()->create([
            'password' => 'password',
            'two_factor_secret' => $this->userSecret,
            'two_factor_confirmed_at' => now(),
        ]);

        $user->assignRole(Roles::PMS_PROPONENT);

        $loginResponse = postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ], ['X-Source-System' => Systems::PMS->value]);

        $challengeId = $loginResponse->json('data.challenge_id');

        // 2. Generate Valid Code
        $validCode = $this->google2fa->getCurrentOtp($this->userSecret);

        // Act: Verify
        $response = postJson(route('api.v1.auth.login.challenge'), [
            'challenge_id' => $challengeId,
            'code' => $validCode,
        ], ['X-Source-System' => Systems::PMS->value]);

        // Assert
        $response->assertOk()
            ->assertJsonStructure(['data' => ['access_token']]);

        $response->assertCookie(config('cookie.refresh_token.name'));

        Event::assertDispatched(UserLoggedIn::class);
    });

    it('accepts a valid recovery code and removes it from user', function (): void {
        // Arrange
        $recoveryCodes = collect([
            Str::random(10) . '-' . Str::random(10),
            Str::random(10) . '-' . Str::random(10),
        ]);

        $user = User::factory()
            ->has(UserProfile::factory(), 'profile')
            ->create([
                'password' => 'password',
                'two_factor_secret' => $this->userSecret,
                'two_factor_confirmed_at' => now(),
                'two_factor_recovery_codes' => $recoveryCodes,
            ]);

        $user->assignRole(Roles::PMS_PROPONENT);

        // 1. Get Challenge
        $loginResponse = postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ], ['X-Source-System' => Systems::PMS->value]);

        $challengeId = $loginResponse->json('data.challenge_id');

        // Act: Verify with RECOVERY code
        $response = postJson(route('api.v1.auth.login.challenge'), [
            'challenge_id' => $challengeId,
            'code' => $recoveryCodes->first(), // Using the first one
        ], ['X-Source-System' => Systems::PMS->value]);

        // Assert
        $response->assertOk()
            ->assertJsonStructure(['data' => ['access_token']]);

        // Verify code was burned (removed)
        $user->refresh();
        $remainingCodes = $user->two_factor_recovery_codes;

        expect($remainingCodes)->not->toContain($recoveryCodes->first())
            ->and($remainingCodes)->toContain($recoveryCodes->skip(1)->first());
    });
});

describe('2FA Feature: The Unhappy Path', function (): void {

    it('rejects an invalid OTP code', function (): void {
        $user = User::factory()->create([
            'password' => 'password',
            'two_factor_secret' => $this->userSecret,
            'two_factor_confirmed_at' => now(),
        ]);

        $user->assignRole(Roles::PMS_PROPONENT);

        $loginResponse = postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ], ['X-Source-System' => Systems::PMS->value]);

        $challengeId = $loginResponse->json('data.challenge_id');

        // Act: Send wrong code
        $response = postJson(route('api.v1.auth.login.challenge'), [
            'challenge_id' => $challengeId,
            'code' => '000000', // Wrong
        ], ['X-Source-System' => Systems::PMS->value]);

        // Assert
        $response->assertForbidden();
    });

    it('rejects an invalid recovery code', function (): void {
        $user = User::factory()->create([
            'password' => 'password',
            'two_factor_secret' => $this->userSecret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => collect(['1111111111-1111111111']),
        ]);

        $user->assignRole(Roles::PMS_PROPONENT);

        $challengeId = postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ], ['X-Source-System' => Systems::PMS->value])->json('data.challenge_id');

        // Act
        $response = postJson(route('api.v1.auth.login.challenge'), [
            'challenge_id' => $challengeId,
            'code' => '0000000000-0000000000',
        ], ['X-Source-System' => Systems::PMS->value]);

        // Assert
        $response->assertForbidden();
    });

    it('prevents replay attacks (using same OTP twice)', function (): void {
        // Note: Standard TOTP allows the code to be valid for ~30 seconds.

        $user = User::factory()
            ->has(UserProfile::factory(), 'profile')
            ->create([
                'password' => 'password',
                'two_factor_secret' => $this->userSecret,
                'two_factor_confirmed_at' => now(),
            ]);

        $user->assignRole(Roles::PMS_PROPONENT);

        $challengeId = postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ], ['X-Source-System' => Systems::PMS->value])->json('data.challenge_id');

        $validCode = $this->google2fa->getCurrentOtp($this->userSecret);

        // 1. First Success
        postJson(route('api.v1.auth.login.challenge'), [
            'challenge_id' => $challengeId,
            'code' => $validCode,
        ], ['X-Source-System' => Systems::PMS->value])->assertOk();

        // 2. Second Attempt (Same Code, Immediate)
        $response = postJson(route('api.v1.auth.login.challenge'), [
            'challenge_id' => $challengeId,
            'code' => $validCode,
        ], ['X-Source-System' => Systems::PMS->value]);

        // Should fail because challenge is invalidated
        // challenge_id is one-time use
        $response->assertForbidden()
            ->assertJson(['message' => 'Challenge expired or invalid.']);
    });

    it('throttles excessive failed attempts', function (): void {

        RateLimiter::for('auth.login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        $user = User::factory()->create([
            'password' => 'password',
            'two_factor_secret' => $this->userSecret,
            'two_factor_confirmed_at' => now(),
        ]);

        $user->assignRole(Roles::PMS_PROPONENT);

        $challengeId = postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ], ['X-Source-System' => Systems::PMS->value])->json('data.challenge_id');

        // Spam 5 wrong codes
        for ($i = 0; $i < 5; $i++) {
            postJson(route('api.v1.auth.login.challenge'), [
                'challenge_id' => $challengeId,
                'code' => '000000',
            ], ['X-Source-System' => Systems::PMS->value]);
        }

        // 6th Attempt
        $response = postJson(route('api.v1.auth.login.challenge'), [
            'challenge_id' => $challengeId,
            'code' => '000000',
        ], ['X-Source-System' => Systems::PMS->value]);

        $response->assertTooManyRequests();
    });

    it('rejects verification if challenge has expired', function (): void {
        $user = User::factory()->create([
            'password' => 'password',
            'two_factor_secret' => $this->userSecret,
            'two_factor_confirmed_at' => now(),
        ]);

        $user->assignRole(Roles::PMS_PROPONENT);

        $challengeId = postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ], ['X-Source-System' => Systems::PMS->value])->json('data.challenge_id');

        // Fast forward time
        travel(16)->minutes();

        $validCode = $this->google2fa->getCurrentOtp($this->userSecret);

        $response = postJson(route('api.v1.auth.login.challenge'), [
            'challenge_id' => $challengeId,
            'code' => $validCode,
        ], ['X-Source-System' => Systems::PMS->value]);

        $response->assertForbidden()
            ->assertJson(['message' => 'Challenge expired or invalid.']);
    });
});
