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
use Illuminate\Database\Eloquent\Collection;
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
 * @property-read ?string $two_factor_secret
 * @property-read ?Collection $two_factor_recovery_codes
 * @property-read ?CarbonInterface $two_factor_confirmed_at
 * @property-read CarbonInterface $last_login_at
 * @property-read CarbonInterface $email_verified_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read ?UserProfile $profile
 * @property-read ?Collection $socialAccounts
 * @property-read ?Collection $devices
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

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'pending_email_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
        return ! blank($this->two_factor_secret) && ! blank($this->two_factor_confirmed_at);
    }

    public function touchLastLoginAt(): void
    {
        $this->updateQuietly([
            'last_login_at' => now(),
        ]);
    }
}
