<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin\Users;

use App\Http\Requests\Api\V1\Admin\AdminBaseRequest;
use Illuminate\Validation\Rule;
use jeremyaliparo\IntegrationSchemas\Enums\Users\UserStatus;

final class IndexRequest extends AdminBaseRequest
{
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', Rule::enum(UserStatus::class)],
            'sort' => ['nullable', 'string', 'in:created_at,-created_at,full_name,-full_name'],
            'deleted' => ['nullable', 'string', 'in:with,only'],
        ];
    }
}
