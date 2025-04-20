<?php

declare(strict_types=1);

namespace App\Enums;

enum CountryEnum: string
{
    case Germany = 'DE';
    case Czech = 'CZ';

    public function countryName(): string
    {
        return trans('enum.country.' . $this->value);
    }

    /**
     * Get translated options
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->keyBy(fn (self $country) => $country->value)
            ->map(fn (self $country) => $country->countryName())
            ->sort()
            ->toArray();
    }
}
