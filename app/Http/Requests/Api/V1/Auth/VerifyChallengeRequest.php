<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class VerifyChallengeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'challenge_id' => ['required', 'string', 'ulid'],
            'code' => ['required', 'string', 'min:6', 'max:21'],
        ];
    }
}
