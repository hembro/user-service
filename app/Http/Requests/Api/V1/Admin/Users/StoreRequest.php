<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin\Users;

use App\Enums\Roles;
use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Titles;
use App\Http\Requests\Api\V1\Admin\AdminBaseRequest;
use App\Rules\EnsureRoleBelongsToSystem;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class StoreRequest extends AdminBaseRequest
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
            'roles' => ['required', 'array'],
            'roles.*' => [
                'required',
                Rule::enum(Roles::class),
                new EnsureRoleBelongsToSystem($this->attributes->get('system')),
            ],
            'reason' => ['nullable', 'string'],

            'system_context' => ['nullable', 'array'],
        ];
    }
}
