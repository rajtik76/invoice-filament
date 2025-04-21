<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\CountryEnum;
use App\Filament\Resources\AddressResource\Pages;
use App\Models\Address;
use App\Traits\HasEntitiesNavigationGroupTrait;
use App\Traits\HasTranslatedBreadcrumbAndNavigationTrait;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class AddressResource extends Resource
{
    use HasEntitiesNavigationGroupTrait;
    use HasTranslatedBreadcrumbAndNavigationTrait;

    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form->schema(Address::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('street')
                    ->label(trans('label.street'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('city')
                    ->label(trans('label.city'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('zip')
                    ->label(trans('label.zip'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('country')
                    ->label(trans('label.country'))
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (CountryEnum $state): string => $state->countryName()),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalHeading(trans('label.edit_address')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAddresses::route('/'),
        ];
    }

    /**
     * Filter current user
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    /**
     * Create address record for current user
     *
     * @param  array<string, mixed>  $data
     */
    public static function createAddressForCurrentUser(array $data): Address
    {
        return Address::create(Arr::add($data, 'user_id', auth()->id()));
    }
}
