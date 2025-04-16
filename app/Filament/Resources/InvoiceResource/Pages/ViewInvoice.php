<?php

declare(strict_types=1);

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make([
                    TextEntry::make('contract.name')
                        ->label(trans('label.contract')),
                    TextEntry::make('number')
                        ->label(trans('label.invoice_number')),
                ])->columns(),

                Section::make([
                    TextEntry::make('issue_date')
                        ->label(trans('label.issue_date'))
                        ->date('d.m.Y'),
                    TextEntry::make('due_date')
                        ->label(trans('label.due_date'))
                        ->date('d.m.Y'),
                ])->columns(),

                Section::make([
                    TextEntry::make('amount')
                        ->label(trans('label.amount'))
                        ->getStateUsing(fn(Invoice $record): string => number_format((float)$record->taskHours()->sum('hours') * $record->contract->price_per_hour, 2) . ' ' . $record->contract->currency->getCurrencySymbol()),
                ]),
            ]);
    }
}
