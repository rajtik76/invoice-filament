<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->label(trans('base.create_supplier'))
                ->modalHeading(trans('base.create_supplier'))
                ->using(function (array $data): void {
                    SupplierResource::createRecordForCurrentUser($data);
                }),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return trans('navigation.suppliers');
    }
}
