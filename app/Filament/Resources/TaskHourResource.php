<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\InvoiceStatusEnum;
use App\Filament\Forms\TaskForm;
use App\Filament\Resources\TaskHourResource\Pages;
use App\Models\Invoice;
use App\Models\InvoiceHour;
use App\Models\Task;
use App\Models\TaskHour;
use App\Traits\HasGetQueryForCurrentUserTrait;
use App\Traits\HasResourceTranslationsTrait;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

class TaskHourResource extends Resource
{
    use HasGetQueryForCurrentUserTrait;
    use HasResourceTranslationsTrait;

    protected static ?string $model = TaskHour::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->columns(1)
                    ->schema([
                        Forms\Components\Select::make('task_id')
                            ->label(trans('label.task'))
                            ->disabled(function (Pages\ListTaskHours $livewire, ?TaskHour $record): bool {
                                if (static::getFilteredTaskId($livewire) && ! $record) {
                                    return true;
                                }

                                return false;
                            })
                            ->relationship(
                                name: 'task',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query) {
                                    $query->where('user_id', auth()->id())->orderBy('name');
                                })
                            ->default(function (Pages\ListTaskHours $livewire, ?TaskHour $record): ?int {
                                if (! $record) {
                                    return static::getFilteredTaskId($livewire);
                                }

                                return null;
                            })
                            ->createOptionModalHeading(trans('label.create_task'))
                            ->createOptionForm(TaskForm::form())
                            ->createOptionUsing(function (array $data): void {
                                TaskResource::createRecordForCurrentUser($data);
                            })
                            ->createOptionAction(fn (Action $action) => $action->slideOver())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Split::make([
                            Forms\Components\DatePicker::make('date')
                                ->label(trans('label.date'))
                                ->format('d.m.Y')
                                ->default(now())
                                ->required(),

                            Forms\Components\TextInput::make('hours')
                                ->label(trans('label.hours'))
                                ->required()
                                ->numeric()
                                ->minValue(0.5)
                                ->step(0.5),
                        ]),

                        Forms\Components\Textarea::make('comment')
                            ->label(trans('label.comment'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (/** @var Builder<TaskHour> $query */ Builder $query) {
                $query
                    ->with(['invoice'])
                    ->when(request()->get('task'), function (Builder $builder, ?string $task) {
                        $builder->where('task_id', $task);
                    });
            })
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('invoice')
                    ->label(trans('label.invoice'))
                    ->formatStateUsing(fn (TaskHour $record): string => $record->invoice?->number)
                    ->badge()
                    ->color(fn (TaskHour $record): array => match ($record->invoice?->status) {
                        InvoiceStatusEnum::Draft => Color::Blue,
                        default => Color::Green,
                    }),

                Tables\Columns\TextColumn::make('task.name')
                    ->label(trans('label.task'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label(trans('label.date'))
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hours')
                    ->label(trans('label.hours'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label(trans('label.comment'))
                    ->size('xs')
                    ->extraAttributes(['class' => 'italic']),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('task')
                    ->relationship(
                        name: 'task',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query): void {
                            $query->where('user_id', auth()->id())->orderBy('name');
                        })
                    ->attribute('task_id'),

                Tables\Filters\TernaryFilter::make('invoice')
                    ->label(trans('label.invoice'))
                    ->trueLabel(trans('label.filters.task_hours.invoice.true'))
                    ->falseLabel(trans('label.filters.task_hours.invoice.false'))
                    ->placeholder(trans('label.filters.task_hours.invoice.null'))
                    ->queries(
                        true: fn (/** @var Builder<TaskHour> $query */ Builder $query): Builder => $query->has('invoice'),
                        false: fn (/** @var Builder<TaskHour> $query */ Builder $query): Builder => $query->doesntHave('invoice'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalHeading(trans('label.edit_task_hour')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('add_to_invoice')
                        ->label(trans('label.add_to_invoice'))
                        ->icon('heroicon-o-plus')
                        ->color(Color::Green)
                        ->form([
                            Forms\Components\Select::make('invoice_id')
                                ->label(trans('label.invoice'))
                                ->options(Invoice::query()->loggedUser()->where('status', InvoiceStatusEnum::Draft)->pluck('number', 'id'))
                                ->required(),
                        ])
                        ->action(function (array $data, Collection $records): void {
                            // Get invoice id
                            $invoiceId = $data['invoice_id'];

                            // Get invoice contract id
                            $invoiceContractId = Invoice::find($invoiceId)->contract_id;

                            // Check if all records have same contract as invoice
                            /** @var Collection<int, TaskHour> $records */
                            $recordsHaveSameContract = $records->every(fn (TaskHour $record): bool => $record->task->contract_id === $invoiceContractId);

                            if (! $recordsHaveSameContract) {
                                Notification::make()
                                    ->title(trans('notification.task_hour_contract_not_match_invoice_contract'))
                                    ->warning()
                                    ->send();

                                return;
                            }

                            // Assign task hours to an invoice
                            InvoiceHour::upsert($records->map(fn (TaskHour $taskHour): array => ['invoice_id' => $invoiceId, 'task_hour_id' => $taskHour->id])->all(), ['task_hour_id']);
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])->checkIfRecordIsSelectableUsing(fn (TaskHour $record): bool => ! $record->invoice || $record->invoice->status === InvoiceStatusEnum::Draft);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaskHours::route('/'),
        ];
    }

    /**
     * Retrieves the filtered task ID from the given Livewire component's table filters.
     */
    protected static function getFilteredTaskId(Pages\ListTaskHours $livewire): ?int
    {
        $taskId = Arr::first($livewire->getTable()->getFilter('task')->getState());

        return $taskId ? intval($taskId) : null;
    }
}
