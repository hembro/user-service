<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Enums\Roles;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Events\Auth\UserLoggedIn;
use App\Events\Users\UserRegistered;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request as Psr7Request;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Laravel\Socialite\Facades\Socialite;
use Mockery;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {

    seed(RoleAndPermissionSeeder::class);

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
describe('Social Authentication: Happy Path', function (): void {

    it('can retrieve the correct redirect url for a provider', function (): void {
        $mockProvider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $mockProvider->shouldReceive('stateless')->andReturnSelf();
        $mockProvider->shouldReceive('redirect')->andReturnSelf();
        $mockProvider->shouldReceive('getTargetUrl')->andReturn('https://accounts.google.com/o/oauth2/v2/auth?client_id=123');

        Socialite::shouldReceive('driver')->with('google')->andReturn($mockProvider);

        $response = getJson(
            uri: '/api/v1/auth/social/google/redirect',
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk()
            ->assertJsonPath('data.provider', 'google')
            ->assertJsonPath('data.redirect_url', 'https://accounts.google.com/o/oauth2/v2/auth?client_id=123');
    });

    it('registers and logs in a brand new user via social provider', function (): void {
        Event::fake([
            UserRegistered::class,
            UserLoggedIn::class,
        ]);

        mockSocialiteUser('new.user@gmail.com', 'Jane', 'Doe');

        $response = postJson(
            uri: '/api/v1/auth/social/google/callback',
            data: ['code' => 'valid-oauth-code-from-google'],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk()
            ->assertJsonPath('data.user.email', 'new.user@gmail.com');

        // Verify Core User
        $user = User::where('email', 'new.user@gmail.com')->first();
        expect($user)->not->toBeNull()
            ->and($user->password)->toBeNull()
            ->and($user->hasRole(Roles::PMS_PROPONENT))->toBeTrue();

        // Verify Profile
        assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
        ]);

        // Verify Social Identity Link
        assertDatabaseHas('user_social_accounts', [
            'user_id' => $user->id,
            'provider_name' => 'google',
            'provider_id' => 'google-id-123',
        ]);

        Event::assertDispatched(UserRegistered::class);
        Event::assertDispatched(UserLoggedIn::class);
    });

    it('logs in an existing user and links their social account seamlessly', function (): void {
        Event::fake([UserLoggedIn::class, UserRegistered::class]);

        // 1. Create an existing user who previously signed up via normal email/password
        $existingUser = User::factory()->create([
            'email' => 'existing@pms.gov.ph',
            'password' => 'SecurePass123!',
            'status' => UserStatus::ACTIVE,
        ]);

        // 2. They now click "Log in with Google" using the same email
        mockSocialiteUser('existing@pms.gov.ph', 'Existing', 'User');

        $response = postJson(
            uri: '/api/v1/auth/social/google/callback',
            data: ['code' => 'valid-oauth-code-from-google'],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk()
            ->assertJsonPath('data.user.id', $existingUser->id);

        // Verify identity was linked safely
        assertDatabaseHas('user_social_accounts', [
            'user_id' => $existingUser->id,
            'provider_name' => 'google',
            'provider_id' => 'google-id-123',
        ]);

        // Since they already existed, UserRegistered should NOT be dispatched
        Event::assertNotDispatched(UserRegistered::class);
        Event::assertDispatched(UserLoggedIn::class);
    });
});

describe('Social Authentication: Unhappy Path', function (): void {

    it('rejects unsupported social providers with a 404', function (): void {
        getJson('/api/v1/auth/social/github/redirect')
            ->assertBadRequest();
    });

    it('returns 401 Unauthorized if the social code is invalid or expired', function (): void {
        // Mock Guzzle throwing an exception (simulating an invalid code sent to Google)
        $mockProvider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $mockProvider->shouldReceive('stateless')->andReturnSelf();

        $mockProvider->shouldReceive('user')->andThrow(
            new ClientException('Invalid code', new Psr7Request('GET', 'test'), new Psr7Response(400))
        );

        Socialite::shouldReceive('driver')->with('google')->andReturn($mockProvider);

        postJson(
            uri: '/api/v1/auth/social/google/callback',
            data: ['code' => 'expired-or-fake-code'],
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid or expired social authentication code.');
    });

    it('prevents social login if the existing account is banned or inactive', function (): void {
        User::factory()->create([
            'email' => 'banned@pms.gov.ph',
            'status' => UserStatus::BANNED,
        ]);

        mockSocialiteUser('banned@pms.gov.ph', 'Banned', 'User');

        postJson(
            uri: '/api/v1/auth/social/google/callback',
            data: ['code' => 'valid-oauth-code-from-google'],
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid credentials');
    });

    it('requires a code payload in the callback request', function (): void {
        postJson(
            uri: '/api/v1/auth/social/google/callback',
            data: [], // Missing 'code'
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    });
});

// -----------------------------------------------------------------------------
// Test Helpers
// -----------------------------------------------------------------------------

/**
 * Mocks the Laravel Socialite Facade to return fake Google User data.
 */
function mockSocialiteUser(string $email, string $firstName, string $lastName): void
{
    $abstractUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
    $abstractUser->shouldReceive('getId')->andReturn('google-id-123');
    $abstractUser->shouldReceive('getName')->andReturn("{$firstName} {$lastName}");
    $abstractUser->shouldReceive('getEmail')->andReturn($email);
    $abstractUser->shouldReceive('getAvatar')->andReturn('https://google.com/avatar.jpg');

    $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
    $provider->shouldReceive('stateless')->andReturnSelf();
    $provider->shouldReceive('user')->andReturn($abstractUser);

    Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
}
