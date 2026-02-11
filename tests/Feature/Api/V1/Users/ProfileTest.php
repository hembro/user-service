<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1\Users;

use App\Enums\Roles;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Events\Users\UserAvatarUpdated;
use App\Events\Users\UserEmailChanged;
use App\Events\Users\UserEmailChangeRequested;
use App\Events\Users\UserPasswordUpdated;
use App\Events\Users\UserProfileUpdated;
use App\Models\User;
use App\Notifications\VerifyNewEmail;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed(RoleAndPermissionSeeder::class);

    // Create a Standard User
    /** @var User $user */
    $user = User::factory()->create([
        'status' => UserStatus::ACTIVE,
        'password' => 'OldPassword123!',
    ]);

    // Create Profile manually since Factory might not do it
    $user->profile()->create([
        'first_name' => 'Original',
        'last_name' => 'Name',
        'avatar_path' => null,
        'sex' => 'male',
    ]);

    $user->assignRole(Roles::PMS_PROPONENT);

    $this->user = $user;

    // Login
    Passport::actingAs(user: $user, scopes: [Roles::PMS_PROPONENT->value]);
});

describe('User Profile (Self-Service): Happy Path', function (): void {

    it('can view their own profile', function (): void {
        $response = getJson(
            uri: route('api.v1.users.profile'),
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk()
            ->assertJsonPath('data.email', $this->user->email)
            ->assertJsonPath('data.first_name', 'Original');
    });

    it('can update their own profile details', function (): void {
        Event::fake();

        $payload = [
            'first_name' => 'Updated',
            'last_name' => 'Person',
            'sex' => 'female',
        ];

        $response = putJson(
            uri: route('api.v1.users.profile.update'),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk()
            ->assertJsonPath('data.first_name', 'Updated');

        assertDatabaseHas('user_profiles', [
            'user_id' => $this->user->id,
            'first_name' => 'Updated',
        ]);

        Event::assertDispatched(UserProfileUpdated::class);
    });

    it('can change their password', function (): void {
        Event::fake();

        $payload = [
            'current_password' => 'OldPassword123!',
            'password' => 'NewSecurePass1!',
            'password_confirmation' => 'NewSecurePass1!',
        ];

        $response = patchJson(
            uri: route('api.v1.users.profile.password.update'),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk();

        expect(Hash::check('NewSecurePass1!', $this->user->refresh()->password))->toBeTrue();

        Event::assertDispatched(UserPasswordUpdated::class);
    });

    it('can upload an avatar', function (): void {
        Event::fake();
        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = postJson(
            uri: route('api.v1.users.profile.avatar.update'),
            data: ['avatar' => $file],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk();

        // Check if file was stored in correct folder structure

        $path = "avatars/{$this->user->id}/" . $file->hashName();
        Storage::disk('public')->assertExists($path);

        assertDatabaseHas('user_profiles', [
            'user_id' => $this->user->id,
            'avatar_path' => $path,
        ]);

        Event::assertDispatched(UserAvatarUpdated::class);
    });

    it('can request an email change', function (): void {
        Event::fake();
        Notification::fake();

        $newEmail = 'new.email@pms.gov.ph';

        $response = postJson(
            uri: route('api.v1.users.email.change.request'),
            data: [
                'email' => $newEmail,
                'current_password' => 'OldPassword123!',
            ],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk();

        assertDatabaseHas('users', [
            'id' => $this->user->id,
            'pending_email' => $newEmail, // Stored in pending
            'email' => $this->user->email, // Original email unchanged
        ]);

        Event::assertDispatched(UserEmailChangeRequested::class);

        Notification::assertSentTo(
            [$this->user],
            VerifyNewEmail::class
        );
    });

    it('can verify and finalize email change', function (): void {
        Event::fake();

        // 1. Setup Pending State
        $newEmail = 'verified.email@pms.gov.ph';
        $token = 'valid-token-123';

        $this->user->update([
            'pending_email' => $newEmail,
            'pending_email_token' => $token,
        ]);

        // 2. Call Verify Endpoint
        $response = postJson(
            uri: route('api.v1.users.email.change.verify'),
            data: ['token' => $token],
            headers: ['X-Source-System' => Systems::PMS->value]
        );

        $response->assertOk();

        // 3. Assert Changes
        $this->user->refresh();

        expect($this->user->email)->toBe($newEmail)
            ->and($this->user->pending_email)->toBeNull()
            ->and($this->user->pending_email_token)->toBeNull();

        Event::assertDispatched(UserEmailChanged::class);
    });
});

describe('User Profile (Self-Service): Unhappy Path', function (): void {

    it('rejects password change if current password is wrong', function (): void {
        $payload = [
            'current_password' => 'WrongPassword!',
            'password' => 'NewSecurePass1!',
            'password_confirmation' => 'NewSecurePass1!',
        ];

        // FIX: Use patchJson instead of postJson with a 'method' parameter
        patchJson(
            uri: route('api.v1.users.profile.password.update'),
            data: $payload,
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    });

    it('rejects avatar upload if file is too large or wrong type', function (): void {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 100); // PDF not allowed

        postJson(
            uri: route('api.v1.users.profile.avatar.update'),
            data: ['avatar' => $file],
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['avatar']);
    });

    it('rejects email change request with wrong password', function (): void {
        postJson(
            uri: route('api.v1.users.email.change.request'),
            data: [
                'email' => 'new@pms.gov.ph',
                'current_password' => 'WrongPassword!',
            ],
            headers: ['X-Source-System' => Systems::PMS->value]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['current_password']);
    });

    it('rejects email verification with invalid token', function (): void {

        $this->user->update([
            'pending_email' => 'waiting@pms.gov.ph',
            'pending_email_token' => 'real-token',
        ]);

        postJson(
            uri: route('api.v1.users.email.change.verify'),
            data: ['token' => 'fake-token'],
            headers: ['X-Source-System' => Systems::PMS->value]
        )->assertForbidden(); // Or 403/400 depending on your Exception handling
    });
});
