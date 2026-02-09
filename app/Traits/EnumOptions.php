<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;

trait EnumOptions
{
    public static function options(): array
    {
        return array_map(fn (self $case) => [
            'label' => $case->label(),
            'value' => $case->value,
        ], self::cases());
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        if (method_exists($this, 'description')) {
            return $this->description() ?? Str::headline($this->name);
        }

        return Str::headline(mb_strtolower($this->name));
    }
}
