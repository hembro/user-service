<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class UserDevice extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'device_uuid',
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
