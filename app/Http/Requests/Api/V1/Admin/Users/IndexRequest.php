<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin\Users;

use App\Enums\UserStatus;
use App\Http\Requests\Traits\HasSystemAccess;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexRequest extends FormRequest
{
    use HasSystemAccess;

    public function authorize(): bool
    {
        return $this->authorizeSystemAccess();
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', Rule::enum(UserStatus::class)],
            'sort' => ['nullable', 'string', 'in:created_at,-created_at,full_name,-full_name'],
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
