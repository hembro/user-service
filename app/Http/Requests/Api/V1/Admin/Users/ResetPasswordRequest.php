<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin\Users;

use App\Http\Requests\Api\V1\Admin\AdminBaseRequest;
use Illuminate\Validation\Rules\Password;

final class ResetPasswordRequest extends AdminBaseRequest
{
    public function rules(): array
    {
        return [
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
