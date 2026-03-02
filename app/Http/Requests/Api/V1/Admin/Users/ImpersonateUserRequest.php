<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin\Users;

use App\Http\Requests\Api\V1\Admin\AdminBaseRequest;

final class ImpersonateUserRequest extends AdminBaseRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string'],
        ];
    }
}
