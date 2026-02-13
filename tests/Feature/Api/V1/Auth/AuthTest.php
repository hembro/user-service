<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\Enums\Auth\ChallengeType;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;

use function Pest\Laravel\call;
use function Pest\Laravel\postJson;
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
        'minutes' => 60,
    ]);
});

describe('Authentication Feature: The Happy Path', function (): void {

    it('returns a challenge for a new/untrusted device', function (): void {
        // Arrange: User exists, but device is NOT in DB
        $password = 'password123';
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
            'password' => $password,
        ]);

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
                'data' => ['challenge_token', 'challenge_type'],
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
        $response->assertCookie(config('cookie.refresh_token.name'));
    });

    it('refreshes token using a valid cookie', function (): void {
        // Arrange: We need a valid Refresh Token Cookie first.
        // We simulate a Trusted Login to get it naturally.
        $password = 'password123';
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
            'password' => $password,
        ]);

        $this->mock(DeviceTrustVerifier::class)
            ->shouldReceive('isTrusted')
            ->andReturnTrue();

        $loginResponse = postJson(
            uri: route('api.v1.auth.login'),
            data: ['email' => $user->email, 'password' => $password],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $cookieName = config('cookie.refresh_token.name');
        $cookieValue = $loginResponse->getCookie($cookieName, false)->getValue();
        $originalAccessToken = $loginResponse->json('data.access_token');

        // Act: Call Refresh Endpoint
        // Note: We use call() because assertJson doesn't support sending cookies easily in some versions
        $response = call(
            method: 'POST',
            uri: route('api.v1.auth.refresh'),
            cookies: [$cookieName => $cookieValue],
            server: ['HTTP_X-Source-System' => Systems::PMS->value]
        );

        // Assert
        $response->assertOk();

        expect($response->json('data.access_token'))->not->toBe($originalAccessToken);
    });

    it('logs out and invalidates cookie', function (): void {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'password' => $password,
            'status' => UserStatus::ACTIVE,
        ]);

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

        $token = $loginResponse->json('data.access_token');

        // Act
        $response = withHeader('Authorization', "Bearer {$token}")
            ->postJson(
                uri: route('api.v1.auth.logout'),
                headers: ['X-Source-System' => Systems::PMS->value]
            );

        // Assert
        $response->assertNoContent()
            ->assertCookieExpired(config('cookie.refresh_token.name'));
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

        // The custom exception handler likely returns 401 or 400 depending on config
        // Assuming InvalidCredentialsException renders 401
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

    it('rejects refresh with invalid cookie', function (): void {
        $cookieName = config('cookie.refresh_token.name');

        $response = call(
            method: 'POST',
            uri: route('api.v1.auth.refresh'),
            cookies: [$cookieName => 'this-is-a-fake-token'],
            server: ['HTTP_X-Source-System' => Systems::PMS->value]
        );

        $response->assertUnauthorized()
            ->assertCookieExpired($cookieName);
    });
});
