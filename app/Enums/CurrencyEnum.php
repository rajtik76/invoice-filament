<?php

declare(strict_types=1);

namespace App\Enums;

enum CurrencyEnum: string
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
            ->keyBy(fn (CurrencyEnum $currency) => $currency->value)
            ->map(fn (CurrencyEnum $currency) => $currency->getCurrencySymbol())
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
