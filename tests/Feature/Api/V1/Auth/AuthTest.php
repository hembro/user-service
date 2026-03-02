<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\Enums\Auth\ChallengeType;
use App\Enums\Roles;
use App\Enums\Systems;
use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserLoggedOut;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;
use Laravel\Passport\Client;

use function Pest\Laravel\call;
use function Pest\Laravel\postJson;
use function Pest\Laravel\seed;
use function Pest\Laravel\withHeader;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // 1. Setup Passport Client (The "System")
    $plainSecret = 'my-super-secret-password';

    $client = Client::forceCreate([
        'name' => 'Test Password Client',
        'secret' => Hash::make($plainSecret),
        'provider' => 'users',
        'redirect_uris' => [],
        'grant_types' => ['password', 'refresh_token'],
        'revoked' => false,
    ]);

    // 2. Mock System Configuration
    Config::set('services.passport.frontend_clients', [
        'pms' => [
            'client_id' => $client->id,
            'client_secret' => $plainSecret,
        ],
    ]);

    // 3. Ensure Cookie Config is consistent for tests
    Config::set('cookie.refresh_token', [
        'name' => 'refresh_token',
        'minutes' => 43200,
    ]);

    Config::set('cookie.device_id', [
        'name' => 'device_id',
        'minutes' => 2628000,
    ]);

    seed(RoleAndPermissionSeeder::class);
});

describe('Authentication Feature: The Happy Path', function (): void {

    it('returns a challenge for a new/untrusted device', function (): void {
        // Arrange: User exists, but device is NOT in DB
        $password = 'password123';
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
            'password' => $password,
        ]);

        $user->assignRole(Roles::PMS_PROPONENT);

        // Act: Login without Device Trust setup
        $response = postJson(
            uri: route('api.v1.auth.login'),
            data: [
                'email' => $user->email,
                'password' => $password,
            ],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        // Assert: Expect Challenge, NOT Token
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => ChallengeType::DEVICE_VERIFICATION->message(),
                'data' => [
                    'challenge_type' => ChallengeType::DEVICE_VERIFICATION->value,
                ],
            ])
            ->assertJsonStructure([
                'data' => ['challenge_id', 'challenge_type'],
            ]);

        // Strict: Ensure NO token cookies are set yet
        $response->assertCookieMissing(config('cookie.refresh_token.name'));
    });

    it('logs in and issues tokens for a trusted device', function (): void {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
            'password' => $password,
        ]);

        $user->assignRole(Roles::PMS_PROPONENT);

        // Mock: Force DeviceTrustService to say "Yes, I know this guy"
        $this->mock(DeviceTrustVerifier::class)
            ->shouldReceive('isTrusted')
            ->once()
            ->andReturnTrue();

        // Act
        $response = postJson(
            uri: route('api.v1.auth.login'),
            data: [
                'email' => $user->email,
                'password' => $password,
            ],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        // Assert: Expect Tokens
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'token_type' => 'Bearer',
                ],
            ])
            ->assertJsonStructure([
                'data' => ['access_token', 'expires_in'],
            ]);

        // Strict: Check HttpOnly Cookie
        $response->assertCookie(config('cookie.refresh_token.name'))
            ->assertCookie(config('cookie.device_id.name'));
    });

    it('refreshes token using a valid cookie and trusted device', function (): void {
        // Arrange: We need a valid Refresh Token Cookie first.
        // We simulate a Trusted Login to get it naturally.
        $password = 'password123';
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
            'password' => $password,
        ]);

        $user->assignRole(Roles::PMS_PROPONENT);

        $deviceId = (string) Str::ulid();

        $this->mock(DeviceTrustVerifier::class, function ($mock) use ($deviceId) {
            $mock->shouldReceive('resolveDeviceId')->andReturn($deviceId);
            $mock->shouldReceive('isTrusted')->andReturnTrue();
        });

        $loginResponse = postJson(
            uri: route('api.v1.auth.login'),
            data: ['email' => $user->email, 'password' => $password],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $refreshCookieName = config('cookie.refresh_token.name');
        $refreshCookieValue = $loginResponse->getCookie($refreshCookieName, false)->getValue();

        $deviceIdCookieName = config('cookie.device_id.name');
        $deviceIdCookieValue = $loginResponse->getCookie($deviceIdCookieName, false)->getValue();

        $originalAccessToken = $loginResponse->json('data.access_token');

        // Act: Call Refresh Endpoint
        $response = call(
            method: 'POST',
            uri: route('api.v1.auth.refresh'),
            cookies: [
                $refreshCookieName => $refreshCookieValue,
                $deviceIdCookieName => $deviceIdCookieValue,
            ],
            server: ['HTTP_X-Source-System' => Systems::PMS->value]
        );

        // Assert
        $response->assertOk();

        expect($response->json('data.access_token'))->not->toBe($originalAccessToken);
    });

    it('logs out and invalidates cookie', function (): void {

        Event::fake();

        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'password' => $password,
            'status' => UserStatus::ACTIVE,
        ]);

        $user->assignRole(Roles::PMS_PROPONENT);

        $this->mock(DeviceTrustVerifier::class)
            ->shouldReceive('isTrusted')
            ->andReturnTrue();

        $loginResponse = postJson(
            uri: route('api.v1.auth.login'),
            data: [
                'email' => $user->email,
                'password' => $password,
            ],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $deviceId = $loginResponse->getCookie(config('cookie.device_id.name'), false)->getValue();

        $loginResponse->assertOk()
            ->assertJsonStructure([
                'data' => ['access_token', 'expires_in'],
            ]);

        Event::assertDispatched(UserLoggedIn::class);

        $token = $loginResponse->json('data.access_token');

        $this->mock(DeviceTrustVerifier::class, function ($mock) use ($deviceId) {

            $mock->shouldReceive('resolveDeviceId')
                ->andReturn($deviceId);

            $mock->shouldReceive('isTrusted')
                ->andReturnTrue();

            $mock->shouldReceive('forgetDevice')
                ->andReturn();
        });

        // Act
        $response = withHeader('X-Source-System', Systems::PMS->value)
            ->withHeader('Authorization', "Bearer {$token}")
            ->withCookie(config('cookie.device_id.name'), $deviceId)
            ->postJson(route('api.v1.auth.logout'));

        // Assert
        $response->assertNoContent()
            ->assertCookieExpired(config('cookie.refresh_token.name'));

        Event::assertDispatched(UserLoggedOut::class);
    });
});

describe('Authentication Feature: The Unhappy Path', function (): void {

    it('rejects invalid credentials with 401', function (): void {
        $user = User::factory()->create([
            'password' => 'correct-password',
            'status' => UserStatus::ACTIVE,
        ]);

        $response = postJson(
            uri: route('api.v1.auth.login'),
            data: [
                'email' => $user->email,
                'password' => 'wrong-password',
            ],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertUnauthorized()
            ->assertJson(['success' => false]);
    });

    it('rejects banned users', function (): void {
        $user = User::factory()->create([
            'password' => 'password123',
            'status' => UserStatus::BANNED,
        ]);

        $response = postJson(
            uri: route('api.v1.auth.login'),
            data: [
                'email' => $user->email,
                'password' => 'password123',
            ],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertUnauthorized();
    });

    it('rejects refresh with invalid cookie and invalid device id', function (): void {
        $refreshCookieName = config('cookie.refresh_token.name');
        $deviceCookieName = config('cookie.device_id.name');

        $response = call(
            method: 'POST',
            uri: route('api.v1.auth.refresh'),
            cookies: [
                $refreshCookieName => 'this-is-a-fake-token',
                $deviceCookieName => 'this-is-a-fake-device-id',
            ],
            server: ['HTTP_X-Source-System' => Systems::PMS->value]
        );

        $response->assertUnauthorized()
            ->assertCookieExpired($refreshCookieName);
    });
});
