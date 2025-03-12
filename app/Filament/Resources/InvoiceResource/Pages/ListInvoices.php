<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(trans('base.create_invoice'))
                ->modalHeading(trans('base.create_invoice'))
                ->slideOver()
                ->using(function (array $data): Invoice {
                    return Invoice::create(Arr::add($data, 'user_id', auth()->id()));
                }),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return trans('navigation.invoices');
    }
}
