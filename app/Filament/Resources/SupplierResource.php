<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use App\Traits\HasEntitiesNavigationGroupTrait;
use App\Traits\HasTranslatedBreadcrumbAndNavigationTrait;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class SupplierResource extends Resource
{
    use HasEntitiesNavigationGroupTrait;
    use HasTranslatedBreadcrumbAndNavigationTrait;

    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Supplier::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('label.supplier'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('registration_number')
                    ->label(trans('label.registration_number'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('vat_number')
                    ->label(trans('label.vat'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label(trans('label.email'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label(trans('label.phone'))
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->modalHeading(trans('label.edit_supplier'))
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
            'index' => Pages\ListSuppliers::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    /**
     * Create supplier record for current user
     */
    public static function createRecordForCurrentUser(array $data): Supplier
    {
        return Supplier::create(Arr::add($data, 'user_id', auth()->id()));
    }
}
