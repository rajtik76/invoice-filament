<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\TaskHour;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DailyOverview extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->queryStringIdentifier('daily_overview')
            ->query(
                TaskHour::query()
                    ->where('user_id', auth()->id())
                    ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            )
            ->defaultPaginationPageOption(5)
            ->groups([
                Group::make('date')
                    ->getTitleFromRecordUsing(fn (TaskHour $record): string => $record->date->format('d.m.Y'))
                    ->collapsible(),
            ])
            ->defaultGroup('date')
            ->columns([
                TextColumn::make('date')
                    ->label(trans('label.date'))
                    ->date('d.m.Y'),

                TextColumn::make('task.name')
                    ->label(trans('label.task'))
                    ->badge()
                    ->color(fn (TaskHour $taskHour): array => match ($taskHour->invoice()->exists()) {
                        true => Color::Green,
                        false => Color::Blue,
                    }),

                TextColumn::make('hours')
                    ->label(trans('label.hours'))
                    ->numeric(1)
                    ->suffix(' ' . trans('label.shorts.hours'))
                    ->summarize(Sum::make()
                        ->numeric(1)
                        ->suffix(' ' . trans('label.shorts.hours'))
                    ),
            ]);
    }
}
