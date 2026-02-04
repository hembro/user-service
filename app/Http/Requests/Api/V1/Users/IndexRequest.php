<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Users;

use App\Enums\Systems;
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'system' => $this->header('X-Source-System', ''),
        ]);
    }
}
