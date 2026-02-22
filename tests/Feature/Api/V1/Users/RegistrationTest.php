<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Users;

use App\Enums\Roles;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Events\Users\UserRegistered;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\postJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed(RoleAndPermissionSeeder::class);
});

function validRegistrationPayload(array $overrides = []): array
{
    return array_merge([
        'email' => 'doctor@pms.gov.ph',
        'password' => 'SecureP@ssw0rd123!',
        'password_confirmation' => 'SecureP@ssw0rd123!',
        'title' => 'Dr.',
        'first_name' => 'Jose',
        'middle_name' => 'Protacio',
        'last_name' => 'Rizal',
        'suffix' => 'Jr.',
        'sex' => 'male',
        'mobile_number' => '09171234567',
        'preferences' => ['theme' => 'dark', 'notifications' => true],
        'system' => Systems::PMS->value,
    ], $overrides);
}

describe('User Registration Feature: The Happy Path', function (): void {

    it('registers a user successfully, creates profile, assigns role, and fires event', function (): void {
        Event::fake();

        $payload = validRegistrationPayload();

        $response = postJson(
            uri: route('api.v1.users.register', absolute: false),
            data: $payload,
            headers: [
                'X-Source-System' => Systems::PMS->value,
            ]
        );

        $response->assertCreated();

        assertDatabaseHas('users', [
            'email' => $payload['email'],
            'status' => UserStatus::PENDING->value, // RegisterUser sets PENDING by default
        ]);

        $user = User::query()->where('email', $payload['email'])->first();

        // Verify Password Hashing
        expect(Hash::check($payload['password'], $user->password))->toBeTrue();

        // Verify Profile
        assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'first_name' => 'Jose',
            'last_name' => 'Rizal',
            'mobile_number' => '09171234567',
            // Note: Postgres JSONB might require different assertion, but this works for standard JSON
        ]);

        // Verify Roles
        expect($user->hasRole(Roles::PMS_PROPONENT))->toBeTrue()
            ->and($user->hasRole(Roles::HERDIN_USER))->toBeFalse();

        // Verify Event
        Event::assertDispatched(UserRegistered::class, fn ($event) => $event->user->id === $user->id);
    });

    it('assigns the correct role based on the system', function (string $system, string $expectedRole): void {
        Event::fake();

        $payload = validRegistrationPayload();

        postJson(
            uri: route('api.v1.users.register', absolute: false),
            data: $payload,
            headers: [
                'X-Source-System' => $system,
            ]
        )->assertCreated();

        $user = User::where('email', $payload['email'])->first();
        expect($user->hasRole($expectedRole))->toBeTrue();
    })->with([
        [Systems::PMS->value, Roles::PMS_PROPONENT->value],
        [Systems::HERDIN->value, Roles::HERDIN_USER->value],
        [Systems::PHRR->value, Roles::PHRR_USER->value],
    ]);

    it('validates required fields', function (string $field): void {
        $payload = validRegistrationPayload([$field => '']);

        postJson(
            uri: route('api.v1.users.register', absolute: false),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors([$field]);
    })->with([
        'email',
        'password',
        'first_name',
        'last_name',
        'sex',
    ]);

    it('prevents duplicate email registration', function (): void {
        // Create an existing user (can be Soft Deleted, but unique rule checks deleted_at IS NULL)
        User::factory()->create(['email' => 'duplicate@example.com']);

        $payload = validRegistrationPayload(['email' => 'duplicate@example.com']);

        postJson(
            uri: route('api.v1.users.register', absolute: false),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });

    it('validates password strength', function (): void {
        $payload = validRegistrationPayload(['password' => 'weak', 'password_confirmation' => 'weak']);

        postJson(
            uri: route('api.v1.users.register', absolute: false),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });
});

describe('User Registration Feature: The Unhappy Path', function (): void {

    it('rejects registration if the X-Source-System header is missing', function (): void {
        $payload = validRegistrationPayload();

        postJson(
            uri: route('api.v1.users.register'),
            data: $payload
            // No header
        )->assertBadRequest();
    });

    it('rejects registration if the X-Source-System header contains an invalid value', function (): void {
        $payload = validRegistrationPayload();

        postJson(
            uri: route('api.v1.users.register', absolute: false),
            data: $payload,
            headers: [
                'X-Source-System' => 'HACKER_SYSTEM',
            ]
        )->assertBadRequest();
    });

    it('fails if password confirmation does not match', function (): void {
        $payload = validRegistrationPayload([
            'password' => 'SecureP@ssw0rd123!',
            'password_confirmation' => 'DifferentP@ssw0rd!',
        ]);

        postJson(
            uri: route('api.v1.users.register', absolute: false),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    });

    it('fails if the email is invalid format', function (string $invalidEmail): void {
        $payload = validRegistrationPayload(['email' => $invalidEmail]);

        postJson(
            uri: route('api.v1.users.register', absolute: false),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    })->with([
        'plainaddress',
        '#@%^%#$@#$@#.com',
        '@example.com',
        'Joe Smith <email@example.com>',
        'email.example.com',
    ]);

    it('fails if the sex is not a valid enum value', function (): void {
        $payload = validRegistrationPayload(['sex' => 'attack_helicopter']);

        postJson(
            uri: route('api.v1.users.register', absolute: false),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['sex']);
    });

    it('aborts the entire transaction if profile creation fails', function (): void {
        Log::shouldReceive('debug')
            ->once()
            ->andThrow(new Exception('Simulated Critical Failure'));

        Log::shouldReceive('error')->withAnyArgs(); // Catch any error logs

        $payload = validRegistrationPayload();

        $response = postJson(
            uri: route('api.v1.users.register', absolute: false),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        // Since the exception is thrown inside the Transaction closure, Laravel catches it and returns 500
        $response->assertServerError();

        // Critical: Verify Rollback
        assertDatabaseMissing('users', ['email' => $payload['email']]);
        assertDatabaseMissing('user_profiles', ['first_name' => 'Jose']);
    });
});
