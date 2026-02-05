<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin\Users;

use App\Http\Requests\Traits\HasSystemAccess;
use Illuminate\Foundation\Http\FormRequest;

final class ShowRequest extends FormRequest
{
    use HasSystemAccess;

    public function authorize(): bool
    {
        return $this->authorizeSystemAccess();
    }

    public function rules(): array
    {
        return [
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
