<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Infrastructure\OutboxStatus;
use App\Enums\Infrastructure\RoutingKey;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * @property-read string $id
 * @property-read RoutingKey $event_type
 * @property-read array $payload
 * @property-read OutboxStatus $status
 * @property-read ?string $error
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
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
