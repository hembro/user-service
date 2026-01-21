<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Users\Sex;
use App\Enums\Users\Suffix;
use App\Enums\Users\Titles;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read string $user_id
 * @property-read Titles $title
 * @property-read string $first_name
 * @property-read string $middle_name
 * @property-read string $last_name
 * @property-read Suffix $suffix
 * @property-read Sex $sex
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
        'title' => Titles::class,
        'suffix' => Suffix::class,
        'preferences' => AsArrayObject::class,
    ];
}
