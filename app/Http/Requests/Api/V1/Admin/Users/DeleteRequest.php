<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin\Users;

use App\Http\Requests\Api\V1\Admin\BaseAdminRequest;

final class DeleteRequest extends BaseAdminRequest
{
    public function rules(): array
    {
        return [
            'system' => $this->systemRules(),
        ];
    }
}
