<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContractResource\Pages;

use App\Filament\Resources\ContractResource;
use App\Models\Contract;
use App\Traits\HasListPageTranslationsTrait;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContracts extends ListRecords
{
    use HasListPageTranslationsTrait;

    protected static string $resource = ContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label(trans('label.create_contract'))
                ->modalHeading(trans('label.create_contract'))
                ->slideOver()
                ->using(function (array $data): Contract {
                    return ContractResource::createRecordForCurrentUser($data);
                }),
        ];
    }
}
