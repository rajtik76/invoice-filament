<?php

declare(strict_types=1);

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use App\Traits\HasListPageTranslationsTrait;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSuppliers extends ListRecords
{
    use HasListPageTranslationsTrait;

    protected static string $resource = SupplierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->label(trans('label.create_supplier'))
                ->modalHeading(trans('label.create_supplier'))
                ->using(function (array $data): void {
                    SupplierResource::createRecordForCurrentUser($data);
                }),
        ];
    }
}
