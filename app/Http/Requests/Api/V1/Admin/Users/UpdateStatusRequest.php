<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin\Users;

use App\Http\Requests\Api\V1\Admin\AdminBaseRequest;
use Illuminate\Validation\Rule;
use jeremyaliparo\IntegrationSchemas\Enums\UserStatus;

final class UpdateStatusRequest extends AdminBaseRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(UserStatus::class)],
        ];
    }
}
