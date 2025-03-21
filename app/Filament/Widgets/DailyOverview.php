<?php

namespace App\Filament\Widgets;

use App\Models\TaskHour;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class DailyOverview extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                TaskHour::query()
                    ->select(['id', 'user_id'])
                    ->selectRaw('DATE(date) as day')
                    ->selectRaw('SUM(hours) as sum_hours')
                    ->where('user_id', auth()->id())
                    ->whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->groupByRaw('DATE(date)')
                    ->orderBy('day', 'desc')
            )
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('day')
                    ->label(__('base.date')),
                TextColumn::make('sum_hours')
                    ->label(__('base.hours'))
                    ->numeric(1),
                TextColumn::make('overviews')
                    ->label('Spent hours')
                    ->getStateUsing(function ($record): ?string {
                        return once(fn(): string => TaskHour::with('task')
                            ->where('user_id', auth()->id())
                            ->whereRaw('DATE(date) = ?', $record->day)
                            ->get()
                            ->map(fn(TaskHour $taskHour): string => "{$taskHour->task->name} - {$taskHour->hours}h")
                            ->implode(', '));
                    })
                    ->badge()
                    ->color(Color::Green)
                    ->separator(',')
            ]);
    }
}
