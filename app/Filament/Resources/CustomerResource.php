<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Forms\CustomerForm;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use App\Traits\HasEntitiesNavigationGroupTrait;
use App\Traits\HasResourceTranslationsTrait;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class CustomerResource extends Resource
{
    use HasEntitiesNavigationGroupTrait;
    use HasResourceTranslationsTrait;

    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(CustomerForm::form());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('label.customer'))
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalHeading(trans('label.edit_customer')),
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
            'index' => Pages\ListCustomers::route('/'),
        ];
    }

    /**
     * Get only current user records
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['address'])
            ->where('user_id', auth()->id());
    }

    /**
     * Create customer record
     *
     * @param  array  $data  <string, mixed>
     */
    public static function createRecordForCurrentUser(array $data): Customer
    {
        return Customer::create(Arr::add($data, 'user_id', auth()->id()));
    }
}
