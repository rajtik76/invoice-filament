<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Filament\Resources\BankAccountResource;
use App\Models\Address;
use App\Models\BankAccount;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

class SupplierForm
{
    public static function form(): array
    {
        return [
            Forms\Components\Grid::make()
                ->columns(1)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(trans('label.name'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Split::make([
                        Forms\Components\TextInput::make('email')
                            ->label(trans('label.email'))
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label(trans('label.phone'))
                            ->tel()
                            ->required()
                            ->maxLength(255),
                    ]),

                    Forms\Components\Split::make([
                        Forms\Components\TextInput::make('registration_number')
                            ->label(trans('label.registration_number'))
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('vat_number')
                            ->label(trans('label.vat'))
                            ->required()
                            ->maxLength(255),
                    ]),

                    Forms\Components\Split::make([
                        Address::getSelectWithNewOption(),
                    ]),

                    Forms\Components\Split::make([
                        Select::make('bank_account_id')
                            ->label(trans('label.bank_account'))
                            ->relationship(
                                name: 'bankAccount',
                                modifyQueryUsing: function (Builder $query): void {
                                    $query->where('user_id', auth()->id())
                                        ->orderBy('bank_code')
                                        ->orderBy('bank_name');
                                }
                            )
                            ->getOptionLabelFromRecordUsing(fn (BankAccount $record): string => "{$record->bank_name}, {$record->account_number} / {$record->bank_code}")
                            ->createOptionModalHeading(trans('label.create_bank_account'))
                            ->createOptionForm(BankAccountForm::form())
                            ->createOptionUsing(function (array $data): void {
                                BankAccountResource::createRecordForCurrentUser($data);
                            })
                            ->createOptionAction(fn (Action $action) => $action->slideOver())
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),
                ]),
        ];
    }
}
