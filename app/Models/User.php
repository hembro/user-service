<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Roles;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Observers\UserObserver;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read string $id
 * @property-read UserStatus $status
 * @property-read string $email
 * @property-read string $pending_email
 * @property-read string $pending_email_token
 * @property-read string $password
 * @property-read CarbonInterface $last_login_at
 * @property-read CarbonInterface $email_verified_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read UserProfile $profile
 */
#[ObservedBy([UserObserver::class])]
final class User extends Authenticatable implements MustVerifyEmail, OAuthenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use HasUlids;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'status',
        'email',
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'email_verified_at',
        'last_login_at',
        'pending_email',
        'pending_email_token',
    ];

    protected $hidden = [
        'password',
        'pending_email_token',
    ];

    protected $casts = [
        'status' => UserStatus::class,
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'two_factor_secret' => 'encrypted',
        'two_factor_recovery_codes' => 'encrypted:collection',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(
            related: UserProfile::class,
            foreignKey: 'user_id',
            localKey: 'id',
        );
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(
            related: UserSocialAccount::class,
            foreignKey: 'user_id',
            localKey: 'id'
        );
    }

    public function devices(): HasMany
    {
        return $this->hasMany(
            related: UserDevice::class,
            foreignKey: 'user_id',
            localKey: 'id'
        );
    }

    public function belongsToSystem(Systems $system): bool
    {
        foreach ($this->getRoleNames() as $name) {
            if (Roles::tryFrom($name)?->system() === $system) {
                return true;
            }
        }

        return false;
    }

    public function hasEnabledTwoFactor(): bool
    {
        return $this->two_factor_secret !== null && $this->two_factor_confirmed_at !== null;
    }

    public function touchLastLoginAt(): void
    {
        $this->updateQuietly([
            'last_login_at' => now(),
        ]);
    }

    public function sendPasswordResetNotification($token): void
    {
        // 1. Build the Frontend URL
        $frontendUrl = config('app.frontend.url');
        $emailParam = urlencode($this->email);
        $resetLink = "{$frontendUrl}/reset-password?token={$token}&email={$emailParam}";

        // 2. Build the Command DTO
        // $command = new SendEmailCommand(
        //     id: (string) Str::ulid(),
        //     recipientEmail: $this->email,
        //     templateName: 'auth.password_reset', // The Email Service must have a template named this
        //     variables: [
        //         'reset_link' => $resetLink,
        //         'user_id' => (string) $this->id,
        //     ],
        //     originSystem: request()->header('X-Source-System') ?? 'auth-service',
        //     occurredAt: now()->toIso8601String(),
        // );

        // // 3. Save to Outbox (This guarantees the email command is sent!)
        // OutboxEvent::create([
        //     'event_type' => RoutingKey::COMMAND_SEND_EMAIL->value,
        //     'payload' => $command->toArray(),
        //     'status' => OutboxStatus::PENDING,
        // ]);
    }
}
