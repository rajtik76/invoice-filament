<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\TaskHour;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CurrentMonthHours extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                label: 'Hours in current month',
                value: TaskHour::where('user_id', auth()->id())
                    ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('hours')
            ),
        ];
    }
}
