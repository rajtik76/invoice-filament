<?php

declare(strict_types=1);

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use App\Traits\HasListPageTranslationsTrait;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAddresses extends ListRecords
{
    use HasListPageTranslationsTrait;

    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(trans('label.create_address'))
                ->slideOver()
                ->modalHeading(trans('label.create_address'))
                ->using(function (array $data): void {
                    self::$resource::createAddressForCurrentUser($data);
                }),
        ];
    }
}
