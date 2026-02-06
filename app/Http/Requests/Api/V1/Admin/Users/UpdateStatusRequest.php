<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin\Users;

use App\Enums\UserStatus;
use App\Http\Requests\Api\V1\Admin\BaseAdminRequest;
use Illuminate\Validation\Rule;

final class UpdateStatusRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(UserStatus::class)],
            'system' => $this->systemRules(),
        ];
    }
}
