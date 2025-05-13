<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\LocaleEnum;
use App\Filament\Forms\ContractForm;
use App\Filament\Resources\ContractResource\Pages;
use App\Models\Contract;
use App\Traits\HasEntitiesNavigationGroupTrait;
use App\Traits\HasResourceTranslationsTrait;
use App\ValueObject\ContractSettingsValueObject;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;

class ContractResource extends Resource
{
    use HasEntitiesNavigationGroupTrait;
    use HasResourceTranslationsTrait;

    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(ContractForm::form());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('active')
                    ->label(trans('label.active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label(trans('label.customer'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label(trans('label.supplier'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(trans('label.contract'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('signed_at')
                    ->label(trans('label.signed_at'))
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_per_hour')
                    ->label(trans('label.price_per_hour'))
                    ->formatStateUsing(fn (Contract $record) => Number::currency($record->price_per_hour, $record->currency->value, app()->getLocale()))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading(trans('label.edit_contract'))
                    ->slideOver()
                    ->mutateRecordDataUsing(function (Contract $record, array $data) {
                        $data['settings'] = [];
                        $data['settings']['invoice_locale'] = $record->settings->invoiceLocale->value;
                        $data['settings']['reverse_charge'] = $record->settings->reverseCharge;

                        return $data;
                    })
                    ->using(function (Contract $record, array $data) {
                        // Get settings
                        $settings = $record->settings;

                        // Update settings
                        $settings->invoiceLocale = LocaleEnum::from($data['settings']['invoice_locale']);
                        $settings->reverseCharge = $data['settings']['reverse_charge'];
                        $record->settings = $settings;

                        // Remove settings from data
                        unset($data['settings']);

                        // Update model
                        $record->update($data);

                        return $record;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    /**
     * Create contract for current user
     */
    public static function createRecordForCurrentUser(array $data): Contract
    {
        // Create contract settings
        $settings = new ContractSettingsValueObject(
            reverseCharge: $data['settings']['reverse_charge'],
            invoiceLocale: LocaleEnum::from($data['settings']['invoice_locale'])
        );

        // Set settings in data array
        $data['settings'] = $settings;

        return Contract::create([
            ...$data,
            'user_id' => auth()->id(),
        ]);
    }
}
