<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

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
                    $mergeData = [
                        'user_id' => auth()->id(),
                        'content' => [],
                    ];

                    return Invoice::create($data + $mergeData);
                }),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return trans('navigation.invoices');
    }
}
