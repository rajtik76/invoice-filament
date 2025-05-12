<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;

class BankAccountForm
{
    public static function form(): array
    {
        return [
            Grid::make()
                ->columns(1)
                ->schema([
                    Split::make([
                        TextInput::make('bank_name')
                            ->label(trans('label.bank_name'))
                            ->required()
                            ->maxLength(255),
                    ]),
                    Split::make([
                        Split::make([
                            TextInput::make('account_number')
                                ->label(trans('label.bank_account'))
                                ->required()
                                ->maxLength(255),
                            TextInput::make('bank_code')
                                ->label(trans('label.bank_code'))
                                ->required()
                                ->maxLength(255)
                                ->grow(false),
                        ]),
                    ]),
                    Split::make([
                        TextInput::make('iban')
                            ->label('IBAN')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('swift')
                            ->label('SWIFT')
                            ->required()
                            ->maxLength(255),
                    ]),
                ]),
        ];
    }
}
