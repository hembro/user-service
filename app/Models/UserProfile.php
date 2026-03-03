<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Titles;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property-read string $id
 * @property-read string $user_id
 * @property-read ?string $avatarUrl
 * @property-read string $full_name
 * @property-read ?Titles $title
 * @property-read string $first_name
 * @property-read ?string $middle_name
 * @property-read string $last_name
 * @property-read ?Collection<Suffix> $suffixes
 * @property-read Sex $sex
 * @property-read ?string $mobile_number
 * @property-read ?ArrayObject $preferences
 * @property-read ?CarbonInterface $created_at
 * @property-read ?CarbonInterface $updated_at
 */
final class UserProfile extends Model
{
    use HasFactory;
    use HasUlids;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'title' => Titles::class,
        'suffixes' => AsEnumCollection::class . ':' . Suffix::class,
        'sex' => Sex::class,
        'preferences' => AsArrayObject::class,
    ];

    protected $touches = ['user'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        self::saving(function (UserProfile $profile): void {
            $profile->full_name = collect([
                $profile->title?->value,
                $profile->first_name,
                $profile->middle_name ? mb_substr($profile->middle_name, 0, 1) . '.' : null,
                $profile->last_name,
                $profile->suffixes?->map(fn (Suffix $s) => $s->value)->implode(', '),
            ])->filter()->implode(' ');
        });
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                if (empty($this->avatar_path)) {
                    return null;
                }

                if (Str::startsWith($this->avatar_path, ['http://', 'https://'])) {
                    return $this->avatar_path;
                }

                return Storage::disk('public')->url($this->avatar_path);
            }
        );
    }
}
