<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Models\Contract;
use App\Traits\HasEntitiesNavigationGroupTrait;
use App\Traits\HasTranslatedBreadcrumbAndNavigationTrait;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Number;

class ContractResource extends Resource
{
    use HasEntitiesNavigationGroupTrait;
    use HasTranslatedBreadcrumbAndNavigationTrait;

    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Contract::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('active')
                    ->label(trans('base.active'))
                    ->boolean(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label(trans('base.customer'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label(trans('base.supplier'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(trans('base.contract'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('signed_at')
                    ->label(trans('base.signed_at'))
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price_per_hour')
                    ->label(trans('base.price_per_hour'))
                    ->formatStateUsing(fn (Contract $record) => Number::currency($record->price_per_hour, $record->currency->value, app()->getLocale()))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading(trans('base.edit_contract'))
                    ->slideOver(),
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
        return Contract::create(Arr::add($data, 'user_id', auth()->id()));
    }
}
