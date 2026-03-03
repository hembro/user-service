<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Titles;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

final class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    public function definition(): array
    {
        $firstName = fake()->firstName();
        $middleName = fake()->lastName();
        $lastName = fake()->lastName();

        $suffixEnums = fake()->boolean(30)
            ? [fake()->randomElement(Suffix::cases())]
            : null;

        $suffixString = $suffixEnums ? $suffixEnums[0]->value : '';

        $fullName = mb_trim("{$firstName} " . mb_substr($middleName, 0, 1) . ". {$lastName} {$suffixString}");

        return [
            'title' => fake()->randomElement(Titles::cases()),
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'full_name' => $fullName,
            'suffixes' => $suffixEnums,
            'sex' => fake()->randomElement(Sex::cases()),
            'mobile_number' => fake()->phoneNumber(),
            'preferences' => [],
        ];
    }
}
