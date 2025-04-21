<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\KeyValueOptionsContract;
use App\Traits\HasCurrentUserScopeTrait;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model implements KeyValueOptionsContract
{
    use HasCurrentUserScopeTrait, HasFactory;

    protected $guarded = [];

    /**
     * Key => value options for bank account
     *
     * @return array<int, string>
     */
    public static function getOptions(): array
    {
        return BankAccount::query()
            ->currentUser()
            ->orderBy('bank_name')
            ->orderBy('account_number')
            ->get()
            ->keyBy('id')
            ->map(fn (BankAccount $account) => "{$account->account_number}/{$account->bank_code} - {$account->bank_name}")
            ->toArray();
    }

    /**
     * Get form
     *
     * @return array<int, mixed>
     */
    public static function getForm(): array
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
