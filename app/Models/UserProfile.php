<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Users\Suffix;
use App\Enums\Users\Titles;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
