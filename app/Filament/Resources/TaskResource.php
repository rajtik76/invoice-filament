<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Models\Task;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class TaskResource extends Resource
{
    use HasGetQueryForCurrentUser {
        getEloquentQuery as getTaskQuery;
    }
    use HasTranslatedBreadcrumbAndNavigation;

    protected static ?string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Task::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('contract.customer.name')
                    ->label(trans('base.customer'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(trans('base.task'))
                    ->searchable()
                    ->sortable()
                    ->url(fn (Task $record) => $record->url)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('task_hours_sum_hours')
                    ->label(trans('base.hours'))
                    ->getStateUsing(fn ($record) => number_format(floatval($record->task_hours_sum_hours), 1))
                    ->color('info')
                    ->url(fn (Task $record) => TaskHourResource::getUrl(parameters: ['tableFilters[task][value]' => $record->id])),

                Tables\Columns\ToggleColumn::make('active')
                    ->label(trans('base.active'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->label(trans('base.active'))
                    ->query(fn (Builder $query) => $query->where('active', true))
                    ->default(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->modalHeading(trans('base.edit_task')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label(trans('base.deactivate_task'))
                        ->action(function (Collection $records) {
                            $records->each(function (Task $record) {
                                $record->update(['active' => false]);
                            });
                        }),
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
            'index' => Pages\ListTasks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return self::getTaskQuery()->withSum('taskHours', 'hours');
    }

    /**
     * Create supplier record for current user
     */
    public static function createRecordForCurrentUser(array $data): Task
    {
        return Task::create(Arr::add($data, 'user_id', auth()->id()));
    }
}
