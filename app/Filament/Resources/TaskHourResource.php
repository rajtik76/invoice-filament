<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\TaskHourResource\Pages;
use App\Models\Task;
use App\Models\TaskHour;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class TaskHourResource extends Resource
{
    use HasGetQueryForCurrentUser;
    use HasTranslatedBreadcrumbAndNavigation;

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
                            ->label(__('base.task'))
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
                            ->createOptionModalHeading(__('base.create_task'))
                            ->createOptionForm(Task::getForm())
                            ->createOptionUsing(function (array $data): void {
                                TaskResource::createRecordForCurrentUser($data);
                            })
                            ->createOptionAction(fn (Action $action) => $action->slideOver())
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Split::make([
                            Forms\Components\DatePicker::make('date')
                                ->label(__('base.date'))
                                ->format('d.m.Y')
                                ->default(now())
                                ->required(),

                            Forms\Components\TextInput::make('hours')
                                ->label(__('base.hours'))
                                ->required()
                                ->numeric()
                                ->minValue(0.5)
                                ->step(0.5),
                        ]),

                        Forms\Components\Textarea::make('comment')
                            ->label(__('base.comment'))
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
                    ->label(__('base.invoice'))
                    ->formatStateUsing(fn (TaskHour $record): string => $record->invoice?->number)
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('task.name')
                    ->label(__('base.task'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label(__('base.date'))
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hours')
                    ->label(__('base.hours'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label(__('base.comment'))
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
                    ->label(__('base.invoice'))
                    ->trueLabel(__('base.filters.task_hours.invoice.true'))
                    ->falseLabel(__('base.filters.task_hours.invoice.false'))
                    ->placeholder(__('base.filters.task_hours.invoice.null'))
                    ->queries(
                        true: fn (/** @var Builder<TaskHour> $query */ Builder $query): Builder => $query->has('invoice'),
                        false: fn (/** @var Builder<TaskHour> $query */ Builder $query): Builder => $query->doesntHave('invoice'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
            ])

            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalHeading(__('base.edit_task_hour')),
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
