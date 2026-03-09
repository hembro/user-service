<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use jeremyaliparo\Foundation\Enums\System;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

abstract class AdminBaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var System */
        $system = $this->attributes->get('system');

        /** @var \App\Models\User */
        $user = $this->user();

        $user->loadMissing('roles');

        if (! $user->belongsToSystem($system)) {
            throw new AccessDeniedHttpException(
                "You are not authorized to perform actions for {$system->value}."
            );
        }

        return true;
    }
}
