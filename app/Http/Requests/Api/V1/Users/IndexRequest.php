<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Users;

use App\Enums\Systems;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'system' => ['required', Rule::enum(Systems::class)],
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
