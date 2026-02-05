<?php

declare(strict_types=1);

namespace App\Http\Requests\Traits;

use App\Enums\Systems;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\Rule;

trait HasSystemAccess
{
    protected function mergeSystemHeader(): void
    {
        $this->merge([
            'system' => $this->header('X-Source-System'),
        ]);
    }

    /**
     * Verify the user belongs to the system requested in the header.
     */
    protected function authorizeSystemAccess(): bool
    {
        $system = Systems::tryFrom((string) $this->input('system'));

        if ($system === null) {
            return true; // delegate to the validator
        }

        if (! $this->user()->belongsToSystem($system)) {
            throw new AuthorizationException(
                "You are not authorized to perform actions for {$system->value}."
            );
        }

        return true;
    }

    protected function systemMessages(): array
    {
        return [
            'system.required' => 'The X-Source-System header is missing.',
            'system.enum' => 'The X-Source-System header contains an invalid value.',
        ];
    }

    protected function systemRules(): array
    {
        return [
            'required',
            Rule::enum(Systems::class),
        ];
    }
}
