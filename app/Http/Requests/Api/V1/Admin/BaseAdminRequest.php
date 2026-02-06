<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use App\Http\Requests\Traits\HasSystemAccess;
use Illuminate\Foundation\Http\FormRequest;

abstract class BaseAdminRequest extends FormRequest
{
    use HasSystemAccess;

    final public function authorize(): bool
    {
        return $this->authorizeSystemAccess();
    }

    final public function messages(): array
    {
        return array_merge(
            $this->systemMessages(),
            $this->customMessages()
        );
    }

    protected function prepareForValidation(): void
    {
        $this->mergeSystemHeader();
    }

    protected function customMessages(): array
    {
        return [];
    }
}
