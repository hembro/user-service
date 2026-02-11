<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SocialProviders;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read string $user_id
 * @property-read SocialProviders $provider_name
 * @property-read string $provider_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read User $user
 */
final class UserSocialAccount extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'provider_name',
        'provider_id',
    ];

    protected $casts = [
        'provider_name' => SocialProviders::class,
    ];

    protected $touches = ['user'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            related: User::class,
            foreignKey: 'user_id',
            ownerKey: 'id'
        );
    }
}
