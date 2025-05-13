<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Models\Address;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;

class CustomerForm
{
    public static function form(): array
    {
        return [
            Grid::make()
                ->columns(1)
                ->schema([
                    Address::getSelectWithNewOption(),

                    Split::make([
                        TextInput::make('name')
                            ->label(trans('label.customer'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('registration_number')
                            ->label(trans('label.registration_number'))
                            ->maxLength(255)
                            ->default(null),

                        TextInput::make('vat_number')
                            ->label(trans('label.vat'))
                            ->required()
                            ->maxLength(255),
                    ]),
                ]),
        ];
    }
}
