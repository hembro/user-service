<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin\Users;

use App\Enums\Roles;
use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Titles;
use App\Http\Requests\Api\V1\Admin\BaseAdminRequest;
use App\Rules\EnsureRoleBelongsToSystem;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class StoreRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'min:3', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'title' => ['nullable', 'string', Rule::enum(Titles::class)],
            'first_name' => ['required', 'string', 'min:2', 'max:100'],
            'middle_name' => ['nullable', 'string', 'min:2', 'max:100'],
            'last_name' => ['required', 'string', 'min:2', 'max:100'],
            'suffix' => ['nullable', 'string', Rule::enum(Suffix::class)],
            'sex' => ['required', 'string', Rule::enum(Sex::class)],
            'mobile_number' => ['nullable', 'string', 'min:10', 'max:11'],
            'preferences' => ['nullable', 'array'],
            'roles' => ['required', 'array'],
            'roles.*' => ['required', Rule::enum(Roles::class), new EnsureRoleBelongsToSystem($this->input('system'))],
            'system' => $this->systemRules(),
        ];
    }
}
