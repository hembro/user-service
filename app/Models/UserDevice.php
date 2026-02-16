<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read string $user_id
 * @property-read string $device_id
 * @property-read string $fingerprint_hash
 * @property-read string $name
 * @property-read string $last_ip
 * @property-read string $user_agent
 * @property-read CarbonInterface $last_used_at
 * @property-read CarbonInterface $verified_at
 */
final class UserDevice extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'device_id',
        'fingerprint_hash',
        'name',
        'last_ip',
        'user_agent',
        'last_used_at',
        'verified_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
