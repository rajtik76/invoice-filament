<?php

declare(strict_types=1);

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use App\Traits\HasListPageTranslationsTrait;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomers extends ListRecords
{
    use HasListPageTranslationsTrait;

    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->label(trans('label.create_customer'))
                ->modalHeading(trans('label.create_customer'))
                ->using(function (array $data): void {
                    CustomerResource::createRecordForCurrentUser($data);
                }),
        ];
    }
}
