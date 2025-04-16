<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\CurrencyEnum;
use App\Enums\InvoiceStatusEnum;
use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers\InvoiceHoursRelationManager;
use App\Models\Contract;
use App\Models\Invoice;
use App\Services\GeneratorService;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;

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
                            ->label(trans('label.contract'))
                            ->relationship(
                                name: 'contract',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query): void {
                                    $query->where('user_id', auth()->id())
                                        ->orderBy('name');
                                }
                            )
                            ->createOptionModalHeading(trans('label.create_contract'))
                            ->createOptionForm(Contract::getForm())
                            ->createOptionUsing(function (array $data): void {
                                ContractResource::createRecordForCurrentUser($data);
                            })
                            ->createOptionAction(fn(Action $action) => $action->slideOver())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                if (!$get('number')) {
                                    $set('number', GeneratorService::getInitials(Contract::find($state)->name) . '-' . now()->year . '-' . sprintf('%03d', now()->month));
                                }
                            })
                            ->required()
                            ->visible(fn(?Invoice $record): bool => is_null($record)),

                        Split::make([
                            TextInput::make('number')
                                ->label(trans('label.invoice_number'))
                                ->required()
                                ->maxLength(255)
                                ->unique(modifyRuleUsing: function (Unique $rule, Get $get) {
                                    $rule->where('user_id', auth()->id())
                                        ->where('contract_id', $get('contract_id'))
                                        ->where('number', $get('number'));
                                }),
                        ]),

                        Split::make([
                            Checkbox::make('prepare_hours')
                                ->label(trans('label.prepare_hours'))
                        ])->visible(fn(?Invoice $record): bool => is_null($record)),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(/** @var Builder<Invoice> $query */ Builder $query) => $query->withSum('taskHours', 'hours'))
            ->defaultSort('number', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('contract.name')
                    ->label(trans('label.contract')),

                Tables\Columns\TextColumn::make('number')
                    ->label(trans('label.invoice_number'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('status')
                    ->label(trans('label.status'))
                    ->formatStateUsing(fn(InvoiceStatusEnum $state) => $state->translation())
                    ->badge()
                    ->color(fn(InvoiceStatusEnum $state) => match ($state) {
                        InvoiceStatusEnum::Draft => Color::Blue,
                        InvoiceStatusEnum::Issued => Color::Green,
                    }),

                Tables\Columns\TextColumn::make('issue_date')
                    ->label(trans('label.issue_date'))
                    ->date('d.m.Y'),

                Tables\Columns\TextColumn::make('due_date')
                    ->label(trans('label.due_date'))
                    ->date('d.m.Y'),

                Tables\Columns\TextColumn::make('task_hours_sum_hours')
                    ->label(trans('label.hours'))
                    ->numeric(decimalPlaces: 1)
                    ->getStateUsing(fn(Invoice $record) => $record->task_hours_sum_hours ?? 0)
                    ->color(fn(Invoice $record): ?array => match ($record->status) {
                        InvoiceStatusEnum::Draft => Color::Blue,
                        default => null,
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label(trans('label.amount'))
                    ->money(
                        currency: fn(Invoice $invoice) => $invoice->contract->currency->value,
                        locale: fn(Invoice $invoice) => $invoice->contract->currency === CurrencyEnum::EUR ? 'de' : 'cs'
                    )
                    ->getStateUsing(fn(Invoice $record): float => $record->task_hours_sum_hours * $record->contract->price_per_hour)
                    ->color(fn(Invoice $record): ?array => match ($record->status) {
                        InvoiceStatusEnum::Draft => Color::Blue,
                        default => null,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(trans('label.status'))
                    ->options(InvoiceStatusEnum::translatedCases()),
            ])
            ->actions([
                // ISSUE
                Tables\Actions\Action::make('issue')
                    ->label(trans('label.issue'))
                    ->visible(fn(Invoice $record): bool => $record->status === InvoiceStatusEnum::Draft)
                    ->color(Color::Blue)
                    ->icon('heroicon-o-document-currency-dollar')
                    ->form([
                        Section::make([
                            DatePicker::make('issue_date')
                                ->label(trans('label.issue_date'))
                                ->default(now()),
                            DatePicker::make('due_date')
                                ->label(trans('label.due_date'))
                                ->default(now()->addDays(7)),
                        ])->columns()
                    ])
                    ->action(function(array $data, Invoice $record): void {
                        $record->update([
                            'status' => InvoiceStatusEnum::Issued,
                            'issue_date' => $data['issue_date'],
                            'due_date' => $data['due_date'],
                        ]);
                    }),

                // PDF
                Tables\Actions\Action::make('pdf')
                    ->label(trans('label.pdf'))
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn(Invoice $record): string => route('invoice.pdf', ['invoice' => $record->id]))
                    ->openUrlInNewTab()
                    ->color(Color::Green)
                    ->hidden(fn(Invoice $record): bool => $record->status === InvoiceStatusEnum::Draft),

                // VIEW
                Tables\Actions\ViewAction::make('view')
                    ->label(trans('label.view'))
                    ->visible(fn(Invoice $record): bool => $record->status === InvoiceStatusEnum::Issued),

                // EDIT
                Tables\Actions\EditAction::make('edit')
                    ->modalHeading(trans('label.edit_invoice'))
                    ->hidden(fn(Invoice $record): bool => $record->status === InvoiceStatusEnum::Issued),

                // DELETE
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            InvoiceHoursRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
            'view' => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
