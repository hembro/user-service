<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Roles;
use App\Enums\Systems;
use App\Enums\UserStatus;
use App\Notifications\ResetPasswordLink;
use App\Observers\UserObserver;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Context;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read string $id
 * @property-read UserStatus $status
 * @property-read string $email
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
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'status' => UserStatus::class,
        'password' => 'hashed',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(
            related: UserProfile::class,
            foreignKey: 'user_id',
            localKey: 'id',
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

    public function sendPasswordResetNotification($token): void
    {
        $system = Systems::from(Context::get('source_system'));

        $this->notify(
            instance: new ResetPasswordLink($token, $system)
        );
    }
}
