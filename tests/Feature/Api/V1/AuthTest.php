<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

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
    $plainSecret = 'my-super-secret-password';

    $client = Client::forceCreate([
        'name' => 'Test Password Client',
        'secret' => Hash::make($plainSecret),
        'provider' => 'users',
        'redirect_uris' => [],
        'grant_types' => ['password', 'refresh_token'],
        'owner_type' => null,
        'owner_id' => null,
        'revoked' => false,
    ]);

    Config::set('services.passport.password_client_id', $client->id);
    Config::set('services.passport.password_client_secret', $plainSecret);
});

describe('Authentication Feature: The Happy Path', function (): void {

    it('logs in, receives tokens, and sets cookie', function (): void {
        $password = 'password123';
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
            'password' => $password,
        ]);

        $response = postJson(
            uri: route('api.v1.auth.login', false),
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
            ->assertCookie('refresh_token');
    });

    it('refreshes token using a valid refresh token cookie', function (): void {
        $password = 'password123';
        $user = User::factory()->create([
            'password' => $password,
            'status' => UserStatus::ACTIVE,
        ]);

        $loginResponse = postJson(
            uri: route('api.v1.auth.login', false),
            data: [
                'email' => $user->email,
                'password' => $password,
            ],
            headers: [
                'X-Source-System' => 'pms',
            ]
        );

        $loginResponse->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'token_type',
                    'access_token',
                    'expires_in',
                ],
            ]);

        $originalAccessToken = $loginResponse->json('data.access_token');

        $cookieValue = $loginResponse->getCookie('refresh_token', false)->getValue();

        $response = call(
            method: 'POST',
            uri: route('api.v1.auth.refresh', false),
            cookies: ['refresh_token' => $cookieValue],
            server: ['HTTP_X-Source-System' => 'pms']
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
            uri: route('api.v1.auth.login', false),
            data: [
                'email' => $user->email,
                'password' => $password,
            ],
            headers: [
                'X-Source-System' => 'pms',
            ]
        );

        $token = $loginResponse->json('data.access_token');

        $response = withHeader('Authorization', "Bearer $token")
            ->postJson(route('api.v1.auth.logout'));

        $response->assertNoContent()
            ->assertCookieExpired('refresh_token');
    });

    it('rejects logout if unauthenticated', function (): void {
        $response = postJson(route('api.v1.auth.logout'));

        $response->assertUnauthorized();
    });
});

describe('Authentication Feature: The Unhappy Path', function (): void {

    it('fails the validation with bad inputs', function (): void {
        $response = postJson(route('api.v1.auth.login'), [
            'email' => 'not-an-email',
            'password' => '',
        ]);

        $response->assertUnprocessable();
    });

    it('rejects invalid credentials', function (): void {
        $user = User::factory()->create([
            'password' => 'correct-password',
            'status' => UserStatus::ACTIVE,
        ]);

        $response = postJson(
            uri: route('api.v1.auth.login', false),
            data: [
                'email' => $user->email,
                'password' => 'wrong-password',
            ],
            headers: [
                'X-Source-System' => 'pms',
            ]
        );

        $response->assertUnauthorized();
    });

    it('rejects inactive or banned users', function (): void {
        $user = User::factory()->create([
            'password' => 'password123',
            'status' => UserStatus::BANNED,
        ]);

        $response = postJson(
            uri: route('api.v1.auth.login', false),
            data: [
                'email' => $user->email,
                'password' => 'password123',
            ],
            headers: [
                'X-Source-System' => 'pms',
            ]
        );

        $response->assertUnauthorized();
    });

    it('rejects invalid or tampered refresh tokens', function (): void {
        $response = call(
            method: 'POST',
            uri: route('api.v1.auth.refresh'),
            cookies: ['refresh_token' => 'this-is-a-fake-token'],
            server: ['HTTP_X-Source-System' => 'pms']
        );

        $response->assertUnauthorized()
            ->assertCookieExpired('refresh_token');
    });
});
