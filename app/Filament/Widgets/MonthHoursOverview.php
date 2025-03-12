<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\TaskHour;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonthHoursOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $interval = CarbonPeriod::create(now()->startOfMonth(), '1 day', now());
        $history = collect($interval)
            ->map(function (Carbon $date) {
                return [
                    'date' => $date,
                    'hours' => floatval(TaskHour::where('user_id', auth()->id())
                        ->where('date', $date)
                        ->sum('hours')),
                ];
            });

        return [
            Stat::make(
                label: 'Hours in current month',
                value: TaskHour::where('user_id', auth()->id())
                    ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('hours')
            )
                ->chart($history->pluck('hours')->all())
                ->color('success'),
        ];
    }
}
