<?php

declare(strict_types=1);

namespace App\Filament\Resources\BankAccountResource\Pages;

use App\Filament\Resources\BankAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListBankAccounts extends ListRecords
{
    protected static string $resource = BankAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->label(trans('base.create_bank_account'))
                ->modalHeading(trans('base.create_bank_account'))
                ->using(function (array $data) {
                    BankAccountResource::createRecordForCurrentUser($data);
                }),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return trans('navigation.bank_accounts');
    }
}
