<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Users;

use App\Enums\Systems;
use App\Enums\UserStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'system' => ['required', Rule::enum(Systems::class)],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', Rule::enum(UserStatus::class)],
            'sort' => ['nullable', 'string', 'in:created_at,-created_at,full_name,-full_name'],
        ];
    }

    public function messages(): array
    {
        return [
            'system.required' => 'The system header is required.',
            'system.enum' => 'The system header is invalid.',
        ];
    }

    public function authorize(): bool
    {
        $system = Systems::tryFrom((string) $this->input('system'));

        if ($system === null) {
            return true;
        }

        if (! $this->user()->belongsToSystem($system)) {
            throw new AuthorizationException(
                message: "You are not authorized to view {$system->value} users."
            );
        }

        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'system' => $this->header('X-Source-System', ''),
        ]);
    }
}
