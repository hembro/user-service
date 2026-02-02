<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Titles;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

final class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->randomElement(Titles::cases()),
            'first_name' => $this->faker->firstName(),
            'middle_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'suffix' => $this->faker->randomElement(Suffix::cases()),
            'sex' => $this->faker->randomElement(Sex::cases()),
            'mobile_number' => $this->faker->phoneNumber(),
            'preferences' => [],
        ];
    }
}
