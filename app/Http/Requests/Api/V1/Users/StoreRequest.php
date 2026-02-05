<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Users;

use App\Enums;
use App\Http\Requests\Traits\HasSystemAccess;
use App\Rules\EnsureRoleBelongsToSystem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class StoreRequest extends FormRequest
{
    use HasSystemAccess;

    public function authorize(): bool
    {
        return $this->authorizeSystemAccess();
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email:rfc', 'min:3', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'title' => ['nullable', 'string', Rule::enum(Enums\Titles::class)],
            'first_name' => ['required', 'string', 'min:2', 'max:100'],
            'middle_name' => ['nullable', 'string', 'min:2', 'max:100'],
            'last_name' => ['required', 'string', 'min:2', 'max:100'],
            'suffix' => ['nullable', 'string', Rule::enum(Enums\Suffix::class)],
            'sex' => ['required', 'string', Rule::enum(Enums\Sex::class)],
            'mobile_number' => ['nullable', 'string', 'min:10', 'max:11'],
            'preferences' => ['nullable', 'array'],
            'role' => [
                'required',
                Rule::enum(Enums\Roles::class),
                new EnsureRoleBelongsToSystem($this->input('system')),
            ],
            'system' => $this->systemRules(),
        ];
    }

    public function messages(): array
    {
        return array_merge(
            $this->systemMessages(),
            []
        );
    }

    protected function prepareForValidation(): void
    {
        $this->mergeSystemHeader();
    }
}
