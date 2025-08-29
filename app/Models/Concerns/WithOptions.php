<?php

namespace App\Models\Concerns;

trait WithOptions
{
    public static function options(): array
    {
        return collect(self::cases())->map(fn ($case) => $case->value)->all();
    }

    public static function labelledOptions(): array
    {
        return collect(self::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ])->all();
    }
}
