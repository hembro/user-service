<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin\Users;

use App\Enums\Roles;
use App\Http\Requests\Api\V1\Admin\AdminBaseRequest;
use App\Rules\EnsureRoleBelongsToSystem;
use Illuminate\Validation\Rule;

final class UpdateRoleRequest extends AdminBaseRequest
{
    public function rules(): array
    {
        return [
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => [
                'required',
                Rule::enum(Roles::class),
                new EnsureRoleBelongsToSystem($this->attributes->get('system')),
            ],
        ];
    }
}
