<?php

namespace App\Filament\Resources;

use App\Enums\Currency;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Contract;
use App\Models\Invoice;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvoiceResource extends Resource
{
    use HasGetQueryForCurrentUser;
    use HasTranslatedBreadcrumbAndNavigation;

    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('contract_id')
                            ->label(trans('base.contract'))
                            ->relationship(
                                name: 'contract',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query): void {
                                    $query->where('user_id', auth()->id())
                                        ->orderBy('name');
                                }
                            )
                            ->createOptionModalHeading(trans('base.create_contract'))
                            ->createOptionForm(Contract::getForm())
                            ->createOptionUsing(function (array $data): void {
                                ContractResource::createRecordForCurrentUser($data);
                            })
                            ->createOptionAction(fn (Action $action) => $action->slideOver())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Split::make([
                            TextInput::make('number')
                                ->label(trans('base.invoice_number'))
                                ->required()
                                ->maxLength(255),

                            TextInput::make('year')
                                ->label(trans('base.year'))
                                ->numeric()
                                ->required()
                                ->default(now()->year),

                            TextInput::make('month')
                                ->label(trans('base.month'))
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->maxValue(12)
                                ->default(now()->month),
                        ]),

                        Split::make([
                            DatePicker::make('issue_date')
                                ->label(trans('base.issue_date'))
                                ->required()
                                ->default(now()),

                            DatePicker::make('due_date')
                                ->label(trans('base.due_date'))
                                ->required()
                                ->default(now()->addDays(7)),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('number', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('contract.name')
                    ->label(trans('base.contract')),

                Tables\Columns\TextColumn::make('number')
                    ->label(trans('base.invoice_number'))
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('year')
                    ->label(trans('base.period'))
                    ->sortable(['month', 'year'])
                    ->formatStateUsing(function ($state, Invoice $invoice) {
                        return "{$invoice->year}/{$invoice->month}";
                    }),

                Tables\Columns\TextColumn::make('issue_date')
                    ->label(trans('base.issue_date'))
                    ->date('d.m.Y'),

                Tables\Columns\TextColumn::make('due_date')
                    ->label(trans('base.due_date'))
                    ->date('d.m.Y'),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label(trans('base.amount'))
                    ->money(
                        currency: fn (Invoice $invoice) => $invoice->contract->currency->value,
                        locale: fn (Invoice $invoice) => $invoice->contract->currency === Currency::EUR ? 'de' : 'cs'
                    ),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make('edit')
                    ->modalHeading(trans('base.edit_invoice'))
                    ->slideOver(),
                Tables\Actions\Action::make('pdf')
                    ->label(trans('base.pdf'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Invoice $record): string => route('invoice.pdf', ['invoice' => $record->id]))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListInvoices::route('/'),
        ];
    }
}
