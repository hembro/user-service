<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class ResetPasswordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'min:3', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
