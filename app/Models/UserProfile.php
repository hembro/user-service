<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read string $id
 * @property-read string $user_id
 * @property-read string $avatar_path
 * @property-read Enums\Titles $title
 * @property-read string $first_name
 * @property-read string $middle_name
 * @property-read string $last_name
 * @property-read Enums\Suffix $suffix
 * @property-read Enums\Sex $sex
 * @property-read string $mobile_number
 * @property-read ArrayObject $preferences
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class UserProfile extends Model
{
    use HasFactory;
    use HasUlids;

    protected $fillable = [
        'user_id',
        'avatar_path',
        'title',
        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'mobile_number',
        'preferences',
    ];

    protected $casts = [
        'title' => Enums\Titles::class,
        'suffix' => Enums\Suffix::class,
        'preferences' => AsArrayObject::class,
    ];

    protected $touches = ['user'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function avatarUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->avatar_path
                ? Storage::disk('public')->url($this->avatar_path)
                : null,
        );
    }
}
