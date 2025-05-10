<?php

declare(strict_types=1);

namespace App\Filament\Resources\BankAccountResource\Pages;

use App\Filament\Resources\BankAccountResource;
use App\Traits\HasListPageTranslationsTrait;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBankAccounts extends ListRecords
{
    use HasListPageTranslationsTrait;

    protected static string $resource = BankAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->label(trans('label.create_bank_account'))
                ->modalHeading(trans('label.create_bank_account'))
                ->using(function (array $data) {
                    BankAccountResource::createRecordForCurrentUser($data);
                }),
        ];
    }
}
