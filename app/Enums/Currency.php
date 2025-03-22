<?php

declare(strict_types=1);

namespace App\Enums;

enum Currency: string
{
    case EUR = 'EUR';
    case CZK = 'CZK';

    /**
     * Get currency options
     *
     * @return array<string, string>
     */
    public static function getOptions(): array
    {
        return collect(self::cases())
            ->keyBy(fn (Currency $currency) => $currency->value)
            ->map(fn (Currency $currency) => $currency->getCurrencySymbol())
            ->toArray();
    }

    /**
     * Get currency symbol
     */
    public function getCurrencySymbol(): string
    {
        return match ($this) {
            self::CZK => 'Kč',
            self::EUR => '€',
        };
    }
}
