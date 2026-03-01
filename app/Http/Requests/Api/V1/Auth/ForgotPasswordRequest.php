<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use jeremyaliparo\IntegrationSchemas\Enums\UserStatus;

final class ForgotPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'min:3',
                'max:255',
                Rule::exists('users')->where(function ($query) {
                    return $query->where('status', UserStatus::ACTIVE->value);
                }),
            ],
        ];
    }
}
