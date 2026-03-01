<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Admin;

use App\Contracts\Auth\DeviceTrustVerifier;
use App\Enums\Roles;
use App\Enums\Systems;
use App\Events\Admin\UserDeleted;
use App\Events\Admin\UserImpersonated;
use App\Events\Admin\UserInvited;
use App\Events\Admin\UserPasswordReset;
use App\Events\Admin\UserRestored;
use App\Events\Admin\UserRoleUpdated;
use App\Events\Admin\UserStatusUpdated;
use App\Events\Admin\UserUpdated;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use jeremyaliparo\IntegrationSchemas\Enums\UserStatus;
use Laravel\Passport\Client;
use Laravel\Passport\Passport;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed(RoleAndPermissionSeeder::class);

    /** @var User $admin */
    $admin = User::factory()->create([
        'status' => UserStatus::ACTIVE,
    ]);

    $admin->assignRole(Roles::PMS_ADMIN);

    $this->admin = $admin;

    // Authenticate as PMS Admin
    Passport::actingAs(
        user: $admin,
        scopes: [Roles::PMS_ADMIN->value]
    );

    $this->mock(DeviceTrustVerifier::class, function ($mock) {
        $mock->shouldReceive('resolveDeviceId')
            ->andReturn('test-admin-device-uuid');

        $mock->shouldReceive('isTrusted')
            ->andReturnTrue();
    });
});

describe('Admin User Management: The Happy Path', function (): void {

    it('can create a new user (invite flow)', function (): void {
        Event::fake();

        $payload = [
            'email' => 'new.employee@pms.gov.ph',
            'password' => 'TemporaryP@ss123!',
            'password_confirmation' => 'TemporaryP@ss123!',
            'title' => 'Mr.',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'sex' => 'male',
            'roles' => [Roles::PMS_PROPONENT->value],
        ];

        $response = postJson(
            uri: route('api.v1.admin.users.store'),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertCreated()
            ->assertJsonPath('data.email', $payload['email'])
            ->assertJsonPath('data.status', UserStatus::ACTIVE->value);

        assertDatabaseHas('users', ['email' => $payload['email']]);
        assertDatabaseHas('user_profiles', ['first_name' => 'Juan']);

        Event::assertDispatched(UserInvited::class);
    });

    it('can list users with pagination and filters', function (): void {
        User::factory()->count(5)->create()->each(function ($u) {
            $u->assignRole(Roles::PMS_PROPONENT);
        });

        $response = getJson(
            uri: route('api.v1.admin.users.index') . '?per_page=10&sort=-created_at',
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'email', 'status', 'roles'],
                ],
                'meta' => ['current_page', 'total'],
            ]);
    });

    it('can show a specific user details', function (): void {
        $user = User::factory()->create();
        $user->assignRole(Roles::PMS_PROPONENT);

        $response = getJson(
            uri: route('api.v1.admin.users.show', $user->id),
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id);
    });

    it('can update a user profile', function (): void {
        Event::fake();

        $user = User::factory()->create();

        $user->profile()->create([
            'first_name' => 'Original',
            'last_name' => 'Name',
            'sex' => 'male',
        ]);

        $user->assignRole(Roles::PMS_PROPONENT);

        $payload = [
            'email' => 'updated.email@pms.gov.ph',
            'first_name' => 'UpdatedName',
            'last_name' => 'UpdatedLast',
            'sex' => 'female',
        ];

        $response = putJson(
            uri: route('api.v1.admin.users.update', $user->id),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk();

        assertDatabaseHas('users', ['email' => $payload['email']]);
        assertDatabaseHas('user_profiles', ['first_name' => 'UpdatedName']);

        Event::assertDispatched(UserUpdated::class);
    });

    it('can update a user status (ban/activate)', function (): void {
        Event::fake();

        $user = User::factory()->create(['status' => UserStatus::ACTIVE]);
        $user->assignRole(Roles::PMS_PROPONENT);

        $response = patchJson(
            uri: route('api.v1.admin.users.status.update', $user->id),
            data: ['status' => UserStatus::BANNED->value],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk()
            ->assertJsonPath('data.status', UserStatus::BANNED->value);

        assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => UserStatus::BANNED->value,
        ]);

        Event::assertDispatched(UserStatusUpdated::class);
    });

    it('can update a user role', function (): void {
        Event::fake();

        $user = User::factory()->create();
        $user->assignRole(Roles::PMS_PROPONENT);

        $response = patchJson(
            uri: route('api.v1.admin.users.role.update', $user->id),
            data: ['roles' => [Roles::PMS_DIVISION_CHIEF->value]],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk();

        expect($user->refresh()->hasRole(Roles::PMS_DIVISION_CHIEF))->toBeTrue()
            ->and($user->hasRole(Roles::PMS_PROPONENT))->toBeFalse();

        Event::assertDispatched(UserRoleUpdated::class);
    });

    it('can admin-reset a user password', function (): void {
        Event::fake();

        $user = User::factory()->create();
        $user->assignRole(Roles::PMS_PROPONENT);

        $newPassword = 'NewSecretPassword123!';

        $response = postJson(
            uri: route('api.v1.admin.users.password.reset', $user->id),
            data: [
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk();

        expect(Hash::check($newPassword, $user->refresh()->password))->toBeTrue();

        Event::assertDispatched(UserPasswordReset::class);
    });

    it('can soft delete a user', function (): void {
        Event::fake();

        $user = User::factory()->create();
        $user->assignRole(Roles::PMS_PROPONENT);

        // FIX: Route name 'delete'
        $response = deleteJson(
            uri: route('api.v1.admin.users.delete', $user->id),
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertNoContent();

        assertSoftDeleted('users', ['id' => $user->id]);

        Event::assertDispatched(UserDeleted::class);
    });

    it('can restore a soft deleted user', function (): void {
        Event::fake();

        $user = User::factory()->create();
        $user->assignRole(Roles::PMS_PROPONENT);
        $user->delete();

        // FIX: HTTP PATCH
        $response = patchJson(
            uri: route('api.v1.admin.users.restore', $user->id),
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk();

        expect($user->refresh()->trashed())->toBeFalse();

        Event::assertDispatched(UserRestored::class);
    });

    it('can impersonate a non-admin user', function (): void {
        Event::fake();

        setUpPassportClient();

        $targetUser = User::factory()->create();
        $targetUser->assignRole(Roles::PMS_PROPONENT);

        $response = postJson(
            uri: route('api.v1.admin.auth.impersonate', $targetUser->id),
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk()
            ->assertJsonStructure(['data' => ['access_token']]);

        Event::assertDispatched(UserImpersonated::class);
    });
});

describe('Admin User Management: The Unhappy Path', function (): void {

    it('forbids non-admins from creating users', function (): void {
        /** @var User */
        $regularUser = User::factory()->create();
        $regularUser->assignRole(Roles::PMS_PROPONENT);
        Passport::actingAs($regularUser);

        $payload = [
            'email' => 'fail@pms.gov.ph',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'first_name' => 'Fail',
            'last_name' => 'User',
            'sex' => 'male',
            'roles' => [Roles::PMS_PROPONENT->value],
        ];

        postJson(
            uri: route('api.v1.admin.users.store'),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        )->assertForbidden();
    });

    it('prevents assigning roles that do not belong to the system', function (): void {
        $payload = [
            'email' => 'cross.system@pms.gov.ph',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'first_name' => 'Cross',
            'last_name' => 'System',
            'sex' => 'male',
            'roles' => [Roles::HERDIN_ADMIN->value],
        ];

        postJson(
            uri: route('api.v1.admin.users.store'),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['roles.0']);
    });

    it('prevents deleting yourself', function (): void {
        deleteJson(
            uri: route('api.v1.admin.users.delete', $this->admin->id),
            headers: ['X-Source-System' => Systems::PMS->value]
        )->assertForbidden();
    });

    it('prevents impersonating another admin', function (): void {
        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole(Roles::PMS_ADMIN);

        postJson(
            uri: route('api.v1.admin.auth.impersonate', $otherAdmin->id),
            headers: ['X-Source-System' => Systems::PMS->value]
        )->assertForbidden();
    });

    it('validates unique email on update', function (): void {
        User::factory()->create(['email' => 'existing@pms.gov.ph']);
        $targetUser = User::factory()->create();
        $targetUser->assignRole(Roles::PMS_PROPONENT);

        putJson(
            uri: route('api.v1.admin.users.update', $targetUser->id),
            data: [
                'email' => 'existing@pms.gov.ph',
                'first_name' => 'Test',
                'last_name' => 'User',
                'sex' => 'male',
            ],
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    });
});

/**
 * Helper to setup Passport Client for Impersonation tests
 */
function setUpPassportClient(): void
{
    $password = 'my-super-secret-password';

    $client = Client::forceCreate([
        'name' => 'Test Password Client',
        'secret' => Hash::make($password),
        'provider' => 'users',
        'redirect_uris' => [],
        'grant_types' => ['password', 'refresh_token'],
        'revoked' => false,
    ]);

    Config::set('services.passport.frontend_clients', [
        'pms' => [
            'client_id' => $client->id,
            'client_secret' => $password,
        ],
        'herdin' => [
            'client_id' => $client->id,
            'client_secret' => $password,
        ],
        'phrr' => [
            'client_id' => $client->id,
            'client_secret' => $password,
        ],
    ]);
}
