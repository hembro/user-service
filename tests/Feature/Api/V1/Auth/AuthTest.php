<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

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
    // 1. Create a Passport Client to act as our "Frontend"
    $plainSecret = 'my-super-secret-password';
    $hashedSecret = Hash::make($plainSecret);

    $client = Client::forceCreate([
        'name' => 'Test Password Client',
        'secret' => $hashedSecret,
        'provider' => 'users',
        'redirect_uris' => [],
        'grant_types' => ['password', 'refresh_token'],
        'revoked' => false,
    ]);

    // 2. Mock the Config structure expected by AuthService & RevokeSystemTokens
    // We map ALL systems to this one test client for simplicity
    Config::set('services.passport.frontend_clients', [
        'pms' => [
            'client_id' => $client->id,
            'client_secret' => $plainSecret,
        ],
        'herdin' => [
            'client_id' => $client->id,
            'client_secret' => $plainSecret,
        ],
        'phrr' => [
            'client_id' => $client->id,
            'client_secret' => $plainSecret,
        ],
    ]);
});

describe('Authentication Feature: The Happy Path', function (): void {

    it('logs in, receives tokens, and sets cookie', function (): void {
        $password = 'password123';
        /** @var User $user */
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
            'password' => $password, // Factory usually hashes this automatically
        ]);

        $response = postJson(
            uri: route('api.v1.auth.login', absolute: false),
            data: [
                'email' => $user->email,
                'password' => $password,
            ],
            headers: [
                'X-Source-System' => 'pms',
            ]
        );

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'token_type',
                    'access_token',
                    'expires_in',
                ],
            ])
            ->assertCookie(config('cookie.refresh_token.name')); // Use config name
    });

    it('refreshes token using a valid refresh token cookie', function (): void {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => $password,
            'status' => UserStatus::ACTIVE,
        ]);

        // 1. Login to get the initial Refresh Token Cookie
        $loginResponse = postJson(
            uri: route('api.v1.auth.login', absolute: false),
            data: [
                'email' => $user->email,
                'password' => $password,
            ],
            headers: ['X-Source-System' => 'pms']
        );

        $cookieName = config('cookie.refresh_token.name');
        $cookieValue = $loginResponse->getCookie($cookieName, false)->getValue();
        $originalAccessToken = $loginResponse->json('data.access_token');

        // 2. Attempt Refresh
        $response = call(
            method: 'POST',
            uri: route('api.v1.auth.refresh', absolute: false),
            parameters: [],
            cookies: [$cookieName => $cookieValue],
            server: ['HTTP_X-Source-System' => 'pms'] // Simulate Header in internal call
        );

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'expires_in',
                    'token_type',
                ],
            ]);

        $newAccessToken = $response->json('data.access_token');
        expect($newAccessToken)->not->toBe($originalAccessToken);
    });

    it('logs out and clears cookie', function (): void {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => $password,
            'status' => UserStatus::ACTIVE,
        ]);

        $loginResponse = postJson(
            uri: route('api.v1.auth.login', absolute: false),
            data: [
                'email' => $user->email,
                'password' => $password,
            ],
            headers: ['X-Source-System' => 'pms']
        );

        $token = $loginResponse->json('data.access_token');

        // Logout requires Authorization header
        $response = withHeader('Authorization', "Bearer $token")
            ->postJson(
                uri: route('api.v1.auth.logout'),
                headers: ['X-Source-System' => 'pms']
            );

        $response->assertNoContent()
            ->assertCookieExpired(config('cookie.refresh_token.name'));
    });

    it('rejects logout if unauthenticated', function (): void {
        $response = postJson(
            uri: route('api.v1.auth.logout', absolute: false),
            headers: ['X-Source-System' => 'pms']
        );

        $response->assertUnauthorized();
    });
});

describe('Authentication Feature: The Unhappy Path', function (): void {

    it('fails the validation with bad inputs', function (): void {
        $response = postJson(
            uri: route('api.v1.auth.login', absolute: false),
            data: [
                'email' => 'not-an-email',
                'password' => '',
            ],
            headers: ['X-Source-System' => 'pms']
        );

        $response->assertUnprocessable();
    });

    it('rejects invalid credentials', function (): void {
        $user = User::factory()->create([
            'password' => 'correct-password',
            'status' => UserStatus::ACTIVE,
        ]);

        $response = postJson(
            uri: route('api.v1.auth.login', absolute: false),
            data: [
                'email' => $user->email,
                'password' => 'wrong-password',
            ],
            headers: ['X-Source-System' => 'pms']
        );

        $response->assertUnauthorized();
    });

    it('rejects inactive or banned users', function (): void {
        $user = User::factory()->create([
            'password' => 'password123',
            'status' => UserStatus::BANNED,
        ]);

        $response = postJson(
            uri: route('api.v1.auth.login', absolute: false),
            data: [
                'email' => $user->email,
                'password' => 'password123',
            ],
            headers: ['X-Source-System' => 'pms']
        );

        $response->assertUnauthorized();
    });

    it('rejects invalid or tampered refresh tokens', function (): void {
        $cookieName = config('cookie.refresh_token.name');

        $response = call(
            method: 'POST',
            uri: route('api.v1.auth.refresh'),
            parameters: [],
            cookies: [$cookieName => 'this-is-a-fake-token'],
            server: ['HTTP_X-Source-System' => 'pms']
        );

        $response->assertUnauthorized()
            ->assertCookieExpired($cookieName);
    });
});
