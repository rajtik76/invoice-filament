<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use App\Enums\CurrencyEnum;
use App\Enums\LocaleEnum;
use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\SupplierResource;
use App\Models\Contract;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class ContractForm
{
    public static function form(): array
    {
        return [
            Forms\Components\Fieldset::make('details')
                ->label(trans('label.contract_details'))
                ->schema([
                    Forms\Components\Select::make('customer_id')
                        ->label(trans('label.customer'))
                        ->relationship(
                            name: 'customer',
                            titleAttribute: 'name',
                            modifyQueryUsing: function (Builder $query): void {
                                $query->where('user_id', auth()->id())
                                    ->orderBy('name');
                            }
                        )
                        ->createOptionModalHeading(trans('label.create_customer'))
                        ->createOptionForm(CustomerForm::form())
                        ->createOptionUsing(function (array $data): void {
                            CustomerResource::createRecordForCurrentUser($data);
                        })
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('supplier_id')
                        ->label(trans('label.supplier'))
                        ->relationship(
                            name: 'supplier',
                            titleAttribute: 'name',
                            modifyQueryUsing: function (Builder $query): void {
                                $query->where('user_id', auth()->id())
                                    ->orderBy('name');
                            }
                        )
                        ->createOptionModalHeading(trans('label.create_supplier'))
                        ->createOptionForm(SupplierForm::form())
                        ->createOptionUsing(function (array $data): void {
                            SupplierResource::createRecordForCurrentUser($data);
                        })
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('name')
                        ->label(trans('label.contract_name'))
                        ->required()
                        ->maxLength(255),

                    Forms\Components\DatePicker::make('signed_at')
                        ->label(trans('label.signed_at'))
                        ->required()
                        ->default(now()),

                    Forms\Components\TextInput::make('price_per_hour')
                        ->label(trans('label.price_per_hour'))
                        ->required()
                        ->numeric(),

                    Forms\Components\Select::make('currency')
                        ->label(trans('label.currency'))
                        ->required()
                        ->options(CurrencyEnum::class),

                    Forms\Components\Toggle::make('active')
                        ->label(trans('label.active'))
                        ->required()
                        ->default(true),
                ]),

            Forms\Components\Fieldset::make('settings')
                ->columns(1)
                ->label(trans('label.settings'))
                ->schema([
                    Forms\Components\Select::make('settings.invoice_locale')
                        ->label(trans('label.invoice_locale'))
                        ->options(LocaleEnum::translatedCases())
                        ->selectablePlaceholder(false)
                        ->formatStateUsing(fn (?Contract $record): string => $record?->settings->invoiceLocale->value ?? LocaleEnum::English->value),

                    Forms\Components\Toggle::make('settings.reverse_charge')
                        ->label(trans('label.invoice_reverse_charge'))
                        ->formatStateUsing(fn (?Contract $record): bool => $record?->settings->reverseCharge ?? false),
                ]),
        ];
    }
}
