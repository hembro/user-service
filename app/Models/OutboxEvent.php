<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Infrastructure\OutboxStatus;
use App\Enums\Infrastructure\RoutingKey;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

final class OutboxEvent extends Model
{
    use HasUlids;
    use Prunable;

    protected $fillable = [
        'id',
        'event_type',
        'payload',
        'status',
        'error',
    ];

    protected $casts = [
        'event_type' => RoutingKey::class,
        'payload' => 'array',
        'status' => OutboxStatus::class,
    ];

    public function prunable()
    {
        return self::where('created_at', '<=', now()->subDays(7))
            ->where('status', OutboxStatus::PUBLISHED);
    }
}
