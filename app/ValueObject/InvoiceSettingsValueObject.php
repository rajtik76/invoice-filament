<?php

declare(strict_types=1);

namespace App\ValueObject;

use App\Enums\LocaleEnum;
use JsonSerializable;
use Livewire\Wireable;

final class InvoiceSettingsValueObject implements JsonSerializable, Wireable
{
    public function __construct(
        public bool $reverseCharge,
        public LocaleEnum $invoiceLocale
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'reverseCharge' => $this->reverseCharge,
            'invoiceLocale' => $this->invoiceLocale,
        ];
    }

    public function toLivewire(): array
    {
        return $this->jsonSerialize();
    }

    public static function fromLivewire($value): InvoiceSettingsValueObject
    {
        return new InvoiceSettingsValueObject(
            reverseCharge: $value['reverseCharge'],
            invoiceLocale: LocaleEnum::from($value['invoiceLocale'])
        );
    }
}
