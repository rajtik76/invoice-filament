<?php

declare(strict_types=1);

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Enums\LocaleEnum;
use App\Filament\Resources\InvoiceResource;
use App\ValueObject\InvoiceSettingsValueObject;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Get invoice settings
        /** @var InvoiceSettingsValueObject $settings */
        $settings = $data['settings'];

        $data['settings'] = [];
        $data['settings']['invoice_locale'] = $settings->invoiceLocale->value;
        $data['settings']['reverse_charge'] = $settings->reverseCharge;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['settings'] = new InvoiceSettingsValueObject(
            reverseCharge: $data['settings']['reverse_charge'],
            invoiceLocale: LocaleEnum::from($data['settings']['invoice_locale']));

        return $data;
    }
}
