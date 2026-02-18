<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Auth;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\Enums\Systems;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;
use PragmaRX\Google2FA\Google2FA;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // 1. Setup Passport & Config
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

    $this->google2fa = new Google2FA();
});

describe('2FA Management Feature: The Happy Path', function (): void {

    it('can initialize 2fa setup (generates secret and qr code)', function (): void {
        $password = 'password';
        // Arrange: User with 2FA DISABLED
        /** @var User */
        $user = User::factory()->create([
            'password' => $password,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ]);

        Passport::actingAs($user);

        $this->mock(DeviceTrustVerifier::class, function ($mock) {
            $mock->shouldReceive('resolveDeviceId')
                ->andReturn('trusted-device-id');

            $mock->shouldReceive('isTrusted')
                ->andReturnTrue();
        });

        // Act: Request to Enable
        // This usually generates the secret but DOES NOT activate it yet (Pending State)
        $response = postJson(
            route('api.v1.auth.2fa.enable'),
            ['current_password' => $password],
            [
                'X-Source-System' => Systems::PMS->value,
            ]
        );

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'secret',
                    'qr_code_url',
                ],
            ]);

        // Database Check: Secret should be set, but NOT confirmed
        $user->refresh();
        expect($user->two_factor_secret)->not->toBeNull()
            ->and($user->two_factor_confirmed_at)->toBeNull();
    });

    it('can confirm 2fa activation with a valid OTP code', function (): void {
        // Arrange: User in "Pending" state (Secret set, but not confirmed)
        $plainSecret = $this->google2fa->generateSecretKey();

        /** @var User */
        $user = User::factory()->create([
            'two_factor_secret' => $plainSecret, // Model casts will encrypt this
            'two_factor_confirmed_at' => null,
        ]);

        Passport::actingAs($user);

        // Act: Confirm with Valid Code
        $validCode = $this->google2fa->getCurrentOtp($plainSecret);

        $this->mock(DeviceTrustVerifier::class, function ($mock) {
            $mock->shouldReceive('resolveDeviceId')
                ->andReturn('trusted-device-id');

            $mock->shouldReceive('isTrusted')
                ->andReturnTrue();
        });

        $response = postJson(route('api.v1.auth.2fa.confirm'), [
            'code' => $validCode,
        ], ['X-Source-System' => Systems::PMS->value]);

        // Assert
        $response->assertOk()
            ->assertJsonStructure(['data' => ['recovery_codes']]); // Should return codes on success

        // Database Check: Now it is confirmed
        $user->refresh();
        expect($user->two_factor_confirmed_at)->not->toBeNull()
            ->and($user->two_factor_recovery_codes)->not->toBeNull();
    });

    it('can disable 2fa (requires password confirmation)', function (): void {
        // Arrange: User with 2FA ENABLED
        $password = 'password';
        /** @var User */
        $user = User::factory()->create([
            'password' => $password,
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => collect([
                Str::random(10) . '-' . Str::random(10),
                Str::random(10) . '-' . Str::random(10),
            ]),
        ]);

        Passport::actingAs($user);

        $this->mock(DeviceTrustVerifier::class, function ($mock) {
            $mock->shouldReceive('resolveDeviceId')
                ->andReturn('trusted-device-id');

            $mock->shouldReceive('isTrusted')
                ->andReturnTrue();
        });

        // Act
        $response = postJson(route('api.v1.auth.2fa.disable'), [
            'current_password' => $password,
        ], ['X-Source-System' => Systems::PMS->value]);

        // Assert
        $response->assertOk();

        // Database Check: Data wiped
        $user->refresh();
        expect($user->two_factor_secret)->toBeNull()
            ->and($user->two_factor_confirmed_at)->toBeNull()
            ->and($user->two_factor_recovery_codes)->toBeNull();
    });

    it('can regenerate recovery codes', function (): void {
        // Arrange
        $password = 'password';
        /** @var User */
        $user = User::factory()->create([
            'password' => $password,
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => collect([Str::random(10) . '-' . Str::random(10)]),
        ]);

        Passport::actingAs($user);

        $this->mock(DeviceTrustVerifier::class, function ($mock) {
            $mock->shouldReceive('resolveDeviceId')
                ->andReturn('trusted-device-id');

            $mock->shouldReceive('isTrusted')
                ->andReturnTrue();
        });

        // Act
        $response = postJson(
            route('api.v1.auth.2fa.recovery-codes'),
            [
                'current_password' => $password,
            ],
            [
                'X-Source-System' => Systems::PMS->value,
            ]
        );

        // Assert
        $response->assertOk()
            ->assertJsonStructure(['data' => ['recovery_codes']]);

        $newCodes = $response->json('data.recovery_codes');

        expect($newCodes)->toBeArray()
            ->and($newCodes)->not->toContain('old-code-1');
    });
});

describe('2FA Management Feature: The Unhappy Path', function (): void {

    it('fails to confirm activation with an invalid code', function (): void {
        /** @var User */
        $user = User::factory()->create([
            'two_factor_secret' => $this->google2fa->generateSecretKey(),
            'two_factor_confirmed_at' => null,
        ]);

        Passport::actingAs($user);

        $this->mock(DeviceTrustVerifier::class, function ($mock) {
            $mock->shouldReceive('resolveDeviceId')
                ->andReturn('trusted-device-id');

            $mock->shouldReceive('isTrusted')
                ->andReturnTrue();
        });

        $response = postJson(route('api.v1.auth.2fa.confirm'), [
            'code' => '000000', // Invalid
        ], ['X-Source-System' => Systems::PMS->value]);

        $response->assertUnauthorized()
            ->assertJsonPath('message', 'Invalid two-factor code.');

        // DB Check: Still unconfirmed
        expect($user->refresh()->two_factor_confirmed_at)->toBeNull();
    });

    it('fails to disable 2fa with incorrect password', function (): void {
        /** @var User */
        $user = User::factory()->create([
            'password' => 'CorrectPassword',
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(),
        ]);

        Passport::actingAs($user);

        $this->mock(DeviceTrustVerifier::class, function ($mock) {
            $mock->shouldReceive('resolveDeviceId')
                ->andReturn('trusted-device-id');

            $mock->shouldReceive('isTrusted')
                ->andReturnTrue();
        });

        $response = postJson(route('api.v1.auth.2fa.disable'), [
            'current_password' => 'WrongPassword',
        ], ['X-Source-System' => Systems::PMS->value]);

        $response->assertInvalid(['current_password']);

        // DB Check: Still enabled
        expect($user->refresh()->two_factor_secret)->not->toBeNull();
    });

    it('cannot confirm 2fa if it is already enabled', function (): void {
        /** @var User */
        $user = User::factory()->create([
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(), // Already confirmed
        ]);

        Passport::actingAs($user);

        $this->mock(DeviceTrustVerifier::class, function ($mock) {
            $mock->shouldReceive('resolveDeviceId')
                ->andReturn('trusted-device-id');

            $mock->shouldReceive('isTrusted')
                ->andReturnTrue();
        });

        // Try to confirm again
        $response = postJson(route('api.v1.auth.2fa.confirm'), [
            'code' => '123456',
        ], ['X-Source-System' => Systems::PMS->value]);

        $response->assertUnauthorized()
            ->assertJsonPath('message', 'Two-factor authentication is already enabled.');
    });

    it('rejects recovery code regeneration with incorrect password', function (): void {
        /** @var User */
        $user = User::factory()->create([
            'password' => 'CorrectPassword',
            'two_factor_secret' => 'secret',
            'two_factor_confirmed_at' => now(),
        ]);

        Passport::actingAs($user);

        $this->mock(DeviceTrustVerifier::class, function ($mock) {
            $mock->shouldReceive('resolveDeviceId')
                ->andReturn('trusted-device-id');

            $mock->shouldReceive('isTrusted')
                ->andReturnTrue();
        });

        $response = postJson(route('api.v1.auth.2fa.recovery-codes'), [
            'current_password' => 'WrongPassword',
        ], ['X-Source-System' => Systems::PMS->value]);

        $response->assertInvalid(['current_password']);

        // Ensure codes were NOT generated
        expect($user->refresh()->two_factor_recovery_codes)->toBeNull();
    });
});
