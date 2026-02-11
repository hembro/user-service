<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

final class UpdateAvatarRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'avatar' => [
                'required',
                File::image()
                    ->min('1kb')
                    ->max('5mb')
                    ->types(['jpg', 'jpeg', 'png', 'webp']),
            ],
        ];
    }
}
