<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Enums\CountryEnum;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class AddressForm
{
    public static function form(): array
    {
        return [
            Grid::make()
                ->columns(1)
                ->schema([
                    TextInput::make('street')
                        ->label(trans('label.street'))
                        ->required()
                        ->maxLength(255),
                ]),
            Grid::make()
                ->columns(3)
                ->schema([
                    TextInput::make('city')
                        ->label(trans('label.city'))
                        ->required()
                        ->maxLength(255),
                    TextInput::make('zip')
                        ->label(trans('label.zip'))
                        ->required()
                        ->maxLength(255),
                    Select::make('country')
                        ->label(trans('label.country'))
                        ->required()
                        ->options(CountryEnum::options()),
                ]),
        ];
    }
}
