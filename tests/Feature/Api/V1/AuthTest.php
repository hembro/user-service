<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Enums\Users\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $clientRepository = app(ClientRepository::class);
    $client = $clientRepository->createPasswordGrantClient(
        name: 'Password Grant Client',
        provider: 'users',
    );

    config(['services.passport.password_client_id' => $client->id]);
    config(['services.passport.password_client_secret' => $client->secret]);
});

describe('Authentication Feature: The Happy Path', function (): void {

    it('logs in, receives tokens, and sets cookie', function (): void {
        $password = 'password123';
        $user = User::factory()->create([
            'status' => UserStatus::ACTIVE,
            'password' => $password,
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'expires_in',
                    'token_type',
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

        $loginResponse = $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        $loginResponse->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'expires_in',
                    'token_type',
                ],
            ]);

        $originalAccessToken = $loginResponse->json('data.access_token');

        $cookieValue = $loginResponse->getCookie('refresh_token', false)->getValue();

        $response = $this->call(
            method: 'POST',
            uri: route('api.v1.auth.refresh'),
            cookies: ['refresh_token' => $cookieValue]
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

        $loginResponse = $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        $token = $loginResponse->json('data.access_token');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson(route('api.v1.auth.logout'));

        $response->assertNoContent()
            ->assertCookieExpired('refresh_token');
    });

    it('rejects logout if unauthenticated', function (): void {
        $response = $this->postJson(route('api.v1.auth.logout'));

        $response->assertUnauthorized();
    });
});

describe('Authentication Feature: The Unhappy Path', function (): void {

    it('fails the validation with bad inputs', function (): void {
        $response = $this->postJson(route('api.v1.auth.login'), [
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

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
    });

    it('rejects inactive or banned users', function (): void {
        $user = User::factory()->create([
            'password' => 'password123',
            'status' => UserStatus::BANNED,
        ]);

        $response = $this->postJson(route('api.v1.auth.login'), [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertUnauthorized();
    });

    it('rejects invalid or tampered refresh tokens', function (): void {
        $response = $this->call(
            method: 'POST',
            uri: route('api.v1.auth.refresh'),
            cookies: ['refresh_token' => 'this-is-a-fake-token']
        );

        $response->assertUnauthorized()
            ->assertCookieExpired('refresh_token');
    });
});
