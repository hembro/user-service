<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin\Users;

use App\Enums\Sex;
use App\Enums\Suffix;
use App\Enums\Titles;
use App\Http\Requests\Api\V1\Admin\AdminBaseRequest;
use Illuminate\Validation\Rule;

final class UpdateRequest extends AdminBaseRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'min:3', 'max:255', Rule::unique('users')->ignore($this->route('user')->id)],
            'title' => ['nullable', 'string', Rule::enum(Titles::class)],
            'first_name' => ['required', 'string', 'min:2', 'max:100'],
            'middle_name' => ['nullable', 'string', 'min:2', 'max:100'],
            'last_name' => ['required', 'string', 'min:2', 'max:100'],
            'suffixes' => ['nullable', 'array'],
            'suffixes.*' => ['required', 'string', Rule::enum(Suffix::class)],
            'sex' => ['required', 'string', Rule::enum(Sex::class)],
            'mobile_number' => ['nullable', 'string', 'min:10', 'max:11'],
            'preferences' => ['nullable', 'array'],
            'preferences.theme' => ['nullable', 'string', Rule::in(['light', 'dark', 'system'])],
            'preferences.notifications_enabled' => ['nullable', 'boolean'],

            'system_context' => ['nullable', 'array'],
        ];
    }
}
