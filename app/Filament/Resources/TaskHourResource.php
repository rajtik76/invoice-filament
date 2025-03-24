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
                            ->label(trans('base.task'))
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
                            ->createOptionModalHeading(trans('base.create_task'))
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
                                ->label(trans('base.date'))
                                ->format('d.m.Y')
                                ->default(now())
                                ->required(),

                            Forms\Components\TextInput::make('hours')
                                ->label(trans('base.hours'))
                                ->required()
                                ->numeric()
                                ->minValue(0.5)
                                ->step(0.5),
                        ]),

                        Forms\Components\Textarea::make('comment')
                            ->label(trans('base.comment'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->when(request()->get('task'), function (Builder $q, ?string $task) {
                    $q->where('task_id', $task);
                });
            })
            ->defaultSort('date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('task.name')
                    ->label(trans('base.task'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label(trans('base.date'))
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hours')
                    ->label(trans('base.hours'))
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label(trans('base.comment'))
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
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalHeading(trans('base.edit_task_hour')),
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
