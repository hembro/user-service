<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Users;

use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Titles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'min:3', 'max:255', 'email:rfc', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'title' => ['nullable', 'string', Rule::enum(Titles::class)],
            'first_name' => ['required', 'string', 'min:2', 'max:100'],
            'middle_name' => ['nullable', 'string', 'min:2', 'max:100'],
            'last_name' => ['required', 'string', 'min:2', 'max:100'],
            'suffixes' => ['nullable', 'array'],
            'suffixes.*' => ['required', 'string', Rule::enum(Suffix::class)],
            'sex' => ['required', 'string', Rule::enum(Sex::class)],
            'mobile_number' => ['nullable', 'string', 'min:10', 'max:11'],

            'system_context' => ['nullable', 'array'],
        ];
    }
}
